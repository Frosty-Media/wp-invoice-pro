<?php
class Stats
{
	var $name;
	var $dir;
	var $plugin_dir;
	var $plugin_path;
	
	var $invoiceRange;
	var $invoiceFilter;
	
	var $invoices;
	var $graphTitles;
	var $graphDates;
	var $graphTotals;
	var $graphTitlesOutstanding;
	var $graphTotalsOutstanding;
	var $graphTitlesQuotes;
	var $graphTotalsQuotes;
	
	/**
	 * Stats Constructor
	 *
	 * @author Elliot Condon
	 * @since 2.0.0
	 * 
	 * @param object: Wp3i to find parent variables.
	 **/
	function Stats($parent)
	{
		$this->name = $parent->name;					// Plugin Name
		$this->plugin_dir = $parent->dir;				// Plugin directory
		$this->plugin_path = $parent->path;				// Plugin Absolute Path
		$this->dir = plugins_url('/',__FILE__);			// This directory
		
		return true;	
	}
	
	/**
	 * Setup Variables
	 *
	 * @author Elliot Condon
	 * @since 2.0.0
	 * 
	 **/
	function setup_variables()
	{
		/* Setup Invoice Years (First - Last)
		--------------------------------------*/
		$firstYear = get_posts(array('post_type' => 'invoice', 'numberposts' => '1', 'orderby' => 'date', 'order' => 'ASC' )); 
		$firstYear = $firstYear[0]->post_date; 
		$lastYear = get_posts(array('post_type' => 'invoice', 'numberposts' => '1', 'orderby' => 'date', 'order' => 'DESC' )); 
		$lastYear = $lastYear[0]->post_date; 
		
		$this->invoiceRange['firstYear'] = substr($firstYear,0,4);
		$this->invoiceRange['lastYear'] = substr($lastYear,0,4);
		
		
		/* Determin Filter Start - End
		--------------------------------------*/
		if($_SERVER['REQUEST_METHOD'] == "POST")
		{
			$this->invoiceFilter['financial'] = isset($_POST['financial-year'])? true: false;
			$this->invoiceFilter['month'] = isset($_POST['financial-year'])? '07': '01';
			$this->invoiceFilter['yearStart'] = $_POST['filter-year'];
			$this->invoiceFilter['yearFinish'] = $_POST['filter-year'] + 1;
		}
		else
		{
			$this->invoiceFilter['financial'] = true;
			$this->invoiceFilter['month'] = '07';
			if(date('m') >= 7) // depending on the month, find the current financial years. eg 2010 - 2011
			{
				$this->invoiceFilter['yearStart'] = date('Y');
				$this->invoiceFilter['yearFinish'] = date('Y') + 1;
			}
			else
			{
				$this->invoiceFilter['yearStart'] = date('Y') - 1;
				$this->invoiceFilter['yearFinish'] = date('Y');
			}
		}
		
		add_filter('posts_where', array($this,'financial_year_filter'));
		
		$this->invoices = get_posts(array(
			'post_type' => 'invoice', 
			'numberposts' => '-1', 
			'suppress_filters' => false,
			'meta_key' => 'invoice_number',
			'orderby' => 'invoice_number',
			'order' => 'DESC',
			'post_status' => 'future,publish',
		));
		
		$invoicesReversed = array_reverse($this->invoices);
		$total = 0.00;
		
		// create blank/default graph arrays
		$this->graphDates = array(__('Jan','wp3i'), __('Feb','wp3i'), __('Mar','wp3i'), __('Apr','wp3i'), __('May','wp3i'), __('Jun','wp3i'), __('Jul','wp3i'), __('Aug','wp3i'), __('Sep','wp3i'), __('Oct','wp3i'), __('Nov','wp3i'), __('Dec','wp3i'));
		$this->graphTitles = array('', '', '', '', '', '', '', '', '', '', '', '');
		$this->graphTotals = array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);
		$this->graphTitlesOutstanding = array('', '', '', '', '', '', '', '', '', '', '', '');
		$this->graphTotalsOutstanding = array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);
		$this->graphTitlesQuotes = array('', '', '', '', '', '', '', '', '', '', '', '');
		$this->graphTotalsQuotes = array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);
		
		// add invoice data into array
		foreach($invoicesReversed as $invoice)
		{
			if(get_invoice_type($invoice->ID)==__('Quote','wp3i')) // is quote
			{
				$invoiceMonth = get_the_time('n',$invoice->ID);
				$this->graphTitlesQuotes[$invoiceMonth-1] .= '#'.get_post_meta($invoice->ID, 'invoice_number', true) . '. ' . $invoice->post_title . '<br>';
				$this->graphTotalsQuotes[$invoiceMonth-1] += wp3i_get_invoice_total($invoice->ID);
			}
			elseif(invoice_has_paid($invoice->ID)) // income
			{
				$invoiceMonth = explode('/',get_post_meta($invoice->ID,'invoice_paid',true));
				$invoiceMonth = intval($invoiceMonth[1]);
				
				$this->graphTitles[$invoiceMonth-1] .= '#'.get_post_meta($invoice->ID, 'invoice_number', true) . '. ' . $invoice->post_title . '<br>';
				$this->graphTotals[$invoiceMonth-1] += wp3i_get_invoice_total($invoice->ID);
			}
			elseif(invoice_has_sent($invoice->ID)) // awaiting payment
			{
				$invoiceMonth = explode('/',get_post_meta($invoice->ID,'invoice_sent',true));
				$invoiceMonth = intval($invoiceMonth[1]);
				$this->graphTitlesOutstanding[$invoiceMonth-1] .= '#'.get_post_meta($invoice->ID, 'invoice_number', true) . '. ' . $invoice->post_title . '<br>';
				$this->graphTotalsOutstanding[$invoiceMonth-1] += wp3i_get_invoice_total($invoice->ID);
			}
			
		}
		
		// if financial year is selected, arange the arrays so they start from Jun
		if($this->invoiceFilter['financial'] == true)
		{
			$this->graphDates = array_merge(array_slice($this->graphDates,6,6), array_slice($this->graphDates,0,6));
			$this->graphTitles = array_merge(array_slice($this->graphTitles,6,6), array_slice($this->graphTitles,0,6));
			$this->graphTotals = array_merge(array_slice($this->graphTotals,6,6), array_slice($this->graphTotals,0,6));
			
			$this->graphTitlesOutstanding = array_merge(array_slice($this->graphTitlesOutstanding,6,6), array_slice($this->graphTitlesOutstanding,0,6));
			$this->graphTotalsOutstanding = array_merge(array_slice($this->graphTotalsOutstanding,6,6), array_slice($this->graphTotalsOutstanding,0,6));
			
			$this->graphTitlesQuotes = array_merge(array_slice($this->graphTitlesQuotes,6,6), array_slice($this->graphTitlesQuotes,0,6));
			$this->graphTotalsQuotes = array_merge(array_slice($this->graphTotalsQuotes,6,6), array_slice($this->graphTotalsQuotes,0,6));
		}
			
	}
	
	
	/**
	 * Financial year filter
	 *
	 * @author Elliot Condon
	 * @since 2.0.0
	 * 
	 **/
	function financial_year_filter($where = '') 
	{
		$where .= " AND post_date >= '".$this->invoiceFilter['yearStart']."-".$this->invoiceFilter['month']."-01' AND post_date <= '".$this->invoiceFilter['yearFinish']."-".$this->invoiceFilter['month']."-01'";
		return $where;
	}
	
	
	
	/**
	 * Totals Functions
	 *
	 * @author Elliot Condon
	 * @since 2.0.0
	 * 
	 **/
	function get_stats_total($invoiceStatus)
	{
		$total = 0.00;
		$array;
		if($invoiceStatus == 'paid'){$array = $this->graphTotals;}
		elseif($invoiceStatus == 'sent'){$array = $this->graphTotalsOutstanding;}
		elseif($invoiceStatus == 'quote'){$array = $this->graphTotalsQuotes;}
		else{return 0.00;}
		foreach($array as $amount)
		{
			$total += $amount;
		}
		return number_format($total, 2, '.', '');
	}
	
	
	
	/*--------------------------------------------------------------------------------------------
										wp3i_get_last_financial_year
											
	* If the current date is past June 30, this function will return the current year.
	* If the current date is befor June 30, this function will return last year.
	--------------------------------------------------------------------------------------------*/
	function wp3i_get_last_financial_year()
	{
		if(date('m') >= 7)
		{
			return date('Y');	
		}
		else
		{
			return date('Y') - 1;
		}
	}
	
	function wp3i_date_diff($start, $end) 
	{
		$start_ts = strtotime($start);
		$end_ts = strtotime($end);
		$diff = $end_ts - $start_ts;
		return round($diff / 86400);
	}
	

	function admin_page()
	{
		$this->setup_variables();
		
		//$this->wp3i_filter_invoices();

?>    
<div class="wrap" id="wp3i-stats">
	<div class="wp3i-heading">
        <div class="icon32" id="icon-wp3i"><br></div>
        <h2><?php _e('WordPress 3 Invoice Statistics','wp3i'); ?></h2>
        <form method="post">
            <ul class="subsubsub">
                <li><?php _e('Display from ','wp3i'); ?></li>
                <li><select name="filter-year" id="filter-year">
                    <?php for($i= $this->invoiceRange['lastYear']; $i >= $this->invoiceRange['firstYear']; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php if($i == $this->invoiceFilter['yearStart']){echo 'selected="selected"';} ?>><?php echo $i; ?> - <?php echo $i+1; ?></option>
                    <?php endfor; ?>
                </select></li>
                <li>
                    <input type="checkbox" name="financial-year" id="financial-year" value="yes" <?php if($this->invoiceFilter['financial'] == true){echo 'checked="checked"';} ?> /> <?php _e('Financial Year','wp3i'); ?>
                </li>
                <li><input type="submit" class="button-secondary" value="Filter" id="post-query-submit"></li>
            </ul>
        </form>
    </div>
        <div id="poststuff">
        	
            <div class="postbox" id="wp3i-stats-graph">
                <h3 class="hndle"><span><?php _e('Graph','wp3i'); ?></span></h3>
                <div class="inside">
 
                    	<?php
                        if($this->invoiceFilter['financial'] == true)
                        {
                            $graphTitle = __('Financial Year ','wp3i');
                        }
                        else
                        {
                            $graphTitle =  __('Normal Year ','wp3i');
                        }
                        $graphTitle .= $this->invoiceFilter['yearStart'].' - '.$this->invoiceFilter['yearFinish'];
                        
                        
                        ?>
                        
                        <script type="text/javascript">
                        var chart;
                        var $ = jQuery.noConflict();
						var currency_format = '<?php wp3i_currency_format(); ?>';
                            
                        $(document).ready(function() {
                            chart = new Highcharts.Chart({
                                chart: {
                                    renderTo: 'chart',
                                    defaultSeriesType: 'area',
                                    margin: [50, 20, 40, 80]
                                },
                                title: {
                                    text: '<?php echo $graphTitle;?>',
                                },
                                xAxis: {
                                    categories: [<?php foreach($this->graphDates as $graphDate){echo "'".$graphDate."', ";} ?>],
                                        title: {
                                            text: 'Month'
                                        },
                                        labels: {
                                            formatter: function() {
                                                return this.value;
                                            }
                                        }
                    
                                    },
                                    yAxis: {
                                        title: {
                                            text: null
                                        },
                                        labels: {
                                            formatter: function() {
                                                return '$ '+this.value;
                                            }
                                        }
                                    },
                                    tooltip: {
                                        formatter: function() {
                                                return this.point.name +'<br/>'+
                                                '<b>'+ this.series.name+' '+this.x + ' ' + currency_format.replace('#',this.y)+'</b>';
                                        }
                                    },
                                    legend: {
                                        enabled: false,
                                        
                                    },
                                    colors: [
                                        '#4cc900', 
                                        '#dfdfdf',
                                        '#b1e591'
                                    ],
                                    plotOptions: {
										area: {
											stacking: 'normal'
										}
									},
                                    series: [
                                    {
                                        name: 'Income',
                                        data: [
                                        <?php
                                        for($i = 0; $i < count($this->graphDates); $i++)
                                        {
                                            echo '{';
                                            echo "name: '".$this->graphTitles[$i]."',";
                                            //echo "x: '".$graphDates[$i]."', ";
                                            echo "y: ".$this->graphTotals[$i]."";
                                            echo '}, ';
                                        }
                                        ?>
                                        ]
                                    },{
                                        name: 'Quotes',
                                        data: [
                                        <?php
                                        for($i = 0; $i < count($this->graphDates); $i++)
                                        {
                                            echo '{';
                                            echo "name: '".$this->graphTitlesQuotes[$i]."',";
                                            //echo "x: '".$graphDates[$i]."', ";
                                            echo "y: ".$this->graphTotalsQuotes[$i]."";
                                            echo '}, ';
                                        }
                                        ?>
                                        ]
                                    },{
                                        name: 'Awaiting Payment',
                                        data: [
                                        <?php
                                        for($i = 0; $i < count($this->graphDates); $i++)
                                        {
                                            echo '{';
                                            echo "name: '".$this->graphTitlesOutstanding[$i]."',";
                                            //echo "x: '".$graphDates[$i]."', ";
                                            echo "y: ".$this->graphTotalsOutstanding[$i]."";
                                            echo '}, ';
                                        }
                                        ?>
                                        ]
                                    }]
                                });
                                
                                
                            });
                            </script>
                        <div class="wp3i-stats-graph" id="chart"></div>
                        
                        <div class="summary">
                            <ul>
                                <li class="income">
                                    <h3><span></span><?php _e('Income','wp3i'); ?></h3>
                                    <h2><?php echo wp3i_format_amount($this->get_stats_total('paid')); ?></h2>
                                </li>
                                <li class="awaiting-payment">
                                    <h3><span></span><?php _e('Awaiting Payment','wp3i'); ?></h3>
                                    <h2><?php echo wp3i_format_amount($this->get_stats_total('sent')); ?></h2>
                                </li>
                                <li class="quotes">
                                    <h3><span></span><?php _e('Quotes','wp3i'); ?></h3>
                                    <h2><?php echo wp3i_format_amount($this->get_stats_total('quote')); ?></h2>
                                </li>
                            </ul>
                        </div>
    
                </div>
            </div>
            
            
            <div class="postbox" id="wp3i-stats-list">
                <h3 class="hndle"><span><?php _e('List','wp3i'); ?></span></h3>
                <div class="inside">
                <?php if($this->invoices): $counter = 0;?>
                    <table>
                        <tr>
                            <th><?php _e('Invoice No.','wp3i'); ?></th><th><?php _e('Type','wp3i'); ?></th><th><?php _e('Invoice Name','wp3i'); ?></th><th><?php _e('Total','wp3i'); ?></th><th><?php _e('Date','wp3i'); ?></th><th><?php _e('Client','wp3i'); ?></th><th><?php _e('Status','wp3i'); ?></th>
                        </tr>
                        <?php foreach($this->invoices as $invoice): $counter++; ?>
                        <tr <?php if($counter % 2 == 0){echo 'class="alternate"';} ?>>
                            <td class="invoice-number"><?php echo get_post_meta($invoice->ID, 'invoice_number', true); ?></td>
                            <td class="invoice-type"><?php invoice_type($invoice->ID); ?></td>
                            <td class="invoice-title"><?php edit_post_link($invoice->post_title,'','',$invoice->ID); ?></td>
                            <td class="invoice-total"><?php echo wp3i_format_amount(wp3i_get_invoice_total($invoice->ID)) ?></td>
                            <td class="invoice-date"><?php echo get_the_time('d M Y',$invoice->ID); ?></td>
                            <td class="invoice-cient"><?php echo get_invoice_client_edit($invoice->ID); ?></td>
                            <td><?php
                            $invoice_paid = get_post_meta($invoice->ID, 'invoice_paid', true);
                            $invoice_sent = get_post_meta($invoice->ID, 'invoice_sent', true);
                            if($invoice_paid && $invoice_paid != 'Not yet')
                            {
                                _e('Paid','wp3i');
                            }
                            elseif($invoice_sent && $invoice_sent != 'Not yet')
                            {
                                $invoice_sent = explode('/',$invoice_sent);
                                $invoice_sent = intval($invoice_sent[2]).'-'.intval($invoice_sent[1]).'-'.intval($invoice_sent[0]);
                    
                                $days = $this->wp3i_date_diff($invoice_sent, date_i18n('Y-m-d'));
                                if($days == 0){ _e('Sent today','wp3i');}
                                elseif($days == 1){ _e('Sent 1 day ago','wp3i');}
                                else{ _e('Sent ','wp3i'); echo $days; _e(' days ago','wp3i');} 
                            }
                            else{ _e('Not sent yet','wp3i');}
                            ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif;  ?>
                </div>
            </div>
            
        </div>

</div>
<?php
	}

}
?>