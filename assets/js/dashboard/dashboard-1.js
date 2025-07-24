(function($) {
    /* "use strict" */


 var dzChartlist = function(){
	
	var screenWidth = $(window).width();
		
	var projectChart = function(){
		var options = {
			series: [30, 40, 20, 10],
         chart: {
			type: 'donut',
			width: 250,
		},
		plotOptions: {
			pie: {
			  donut: {
				size: '75%',
				labels: {
					show: true,
					name: {
						show: true,
						offsetY: 12,
					},
					value: {
						show: true,
						fontSize: '24px',
						fontFamily:'Arial',
						fontWeight:'500',
						offsetY: -17,
					},
					total: {
						show: true,
						fontSize: '11px',
						fontWeight:'500',
						fontFamily:'Arial',
						label: 'Delivery',
					   
						formatter: function (w) {
						  return w.globals.seriesTotals.reduce((a, b) => {
							return a + b
						  }, 0)
						}
					}
				}
			  }
			}
		},
		 legend: {
                show: false,
            },
		 colors: ['#FF9F00', 'var(--primary)', '#3AC977','#FF5E5E'],
			labels: ["Compete", "Pending", "Not Start"],
			dataLabels: {
				enabled: false,
			},
        };
	  	var chartBar1 = new ApexCharts(document.querySelector("#projectChart"), options);
		chartBar1.render();
	}
	
	var earningChart = function(){
		
		var chartWidth = $("#earningChart").width();
		/* console.log(chartWidth); */
		
		var options = {
		  series: [
			{
				name: 'Net Profit',
				data: [700,650, 680, 600, 700, 610,710,620],
				/* radius: 30,	 */
			}, 				
		],
			chart: {
			type: 'area',
			height: 250,
			width: chartWidth + 55,
			toolbar: {
				show: false,
			},
			offsetX:-45,
			zoom: {
				enabled: false
			},
			/* sparkline: {
				enabled: true
			} */
			
		},
		
		colors:['var(--primary)'],
		dataLabels: {
		  enabled: false,
		},

		legend: {
			show: false,
		},
		stroke: {
		  show: true,
		  width: 2,
		  curve:'straight',
		  colors:['var(--primary)'],
		},
		grid: {
			show:true,
			borderColor: 'var(--border-color)',
			xaxis: {
				lines: {
					show: true
				}
			},   
			yaxis: {
				lines: {
					show: false
				}
			},  
		},
		yaxis: {
			 show: true,
			 tickAmount: 4,
			  min: 0,
				max: 800,
				labels:{
					offsetX:50,
					style: {
						fontSize: '12px',
						colors: 'var(--title)',
					}
				}
		},
		xaxis: {
			categories: ['','May ', 'June', 'July', 'Aug', 'Sep ', 'Oct'],
			overwriteCategories: undefined,
			axisBorder: {
				show: false,
			},
			axisTicks: {
				show: false
			},
			labels: {
				show: true,
				offsetX:5,
				style: {
					fontSize: '12px',
					colors: 'var(--title)',
				}
			},
		},
		fill: {
		  opacity: 0.5,
		  colors:'var(--primary)',
		  type: 'gradient', 
		  gradient: {
			colorStops:[ 
				
				{
				  offset: 0.6,
				  color: 'var(--primary)',
				  opacity: .2
				},
				{
				  offset: 0.6,
				  color: 'var(--primary)',
				  opacity: .15
				},
				{
				  offset: 100,
				  color: 'white',
				  opacity: 0
				}
			  ],
			  
			}
		},
		tooltip: {
			enabled:true,
			style: {
				fontSize: '12px',
			},
			y: {
				formatter: function(val) {
					return "$" + val + ""
				}
			}
		}
		};

		var chartBar1 = new ApexCharts(document.querySelector("#earningChart"), options);
		chartBar1.render();
		
		$(".earning-chart .nav-link").on('click',function(){
			var seriesType = $(this).attr('data-series');
			var columnData = [];
			switch(seriesType) {
				case "day":
					columnData = [700,650, 680, 650, 700, 610,710,620];
					break;
				case "week":
					columnData = [700,700, 680, 600, 700, 620,710,620];
					break;
				case "month":
					columnData = [700,650, 690, 640, 700, 670,710,620];
					break;
				case "year":
					columnData = [700,650, 590, 650, 700, 610,710,630];
					break;
				default:
					columnData = [700,650, 680, 650, 700, 610,710,620];
			}
			chartBar1.updateSeries([
				{
					name: "Net Profit",
					data: columnData
				}
			]);
		})
	 
	}
	
	var summaryChart = function(){
		var options = {
          series: [{
          name: 'Net Profit',
          data: [44, 55, 57, 56]
        }, 
		{
          name: 'Revenue',
          data: [76, 85, 101, 98]
        }, 
		{
          name: 'Free Cash Flow',
          data: [35, 41, 36, 26]
        }],
          chart: {
          type: 'bar',
          height: 200,
		   toolbar: {
			show: false,
		   }
        },
        plotOptions: {
           bar: {
			horizontal: false,
			columnWidth: '65%',
			endingShape: "rounded",
			borderRadius: 5,
		  },
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: true,
          width: 1,
          colors: ['transparent']
        },
		colors:['var(--primary)', '#bfebe7', '#eee'],
        xaxis: {
			categories: ['Jun24', 'June25', 'June26', 'June27'],
			labels: {
				show: true,
				style: {
					colors: 'var(--title)',
					fontSize: '12px',
					fontFamily: 'poppins',
					fontWeight: 100,
					cssClass: 'apexcharts-xaxis-label',
				},
			},
			axisTicks: {
				show: false,
			},
			crosshairs: {
				show: false,
			},
		},
		legend: {
			show: false,
		},
        yaxis: {
			labels: {
			show: true,
				offsetX:-10,
			   style: {
					colors: 'var(--title)',
					fontSize: '12px',
					fontFamily: 'poppins',
					fontWeight: 100,
					cssClass: 'apexcharts-xaxis-label',
				},
			},
		},
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return "$ " + val + " thousands"
            }
          }
        }
        };

        var summaryChart = new ApexCharts(document.querySelector("#summaryChart"), options);
        summaryChart.render();
		
		$(".summary-chart .nav-link").on('click',function(){
			var seriesType = $(this).attr('data-series');
			var columnData = [];
			var columnData2 = [];
			var columnData3 = [];
			switch(seriesType) {
				case "week":
					columnData = [50, 44, 30, 80];
					columnData2 = [70, 20, 100, 90];
					columnData3 = [40, 45, 30, 25];
					break;
				case "month":
					columnData = [44, 55, 57, 56];
					columnData2 = [76, 85, 101, 98];
					columnData3 = [35, 41, 36, 26];
					break;
				case "today":
					columnData = [100, 90, 70, 80];
					columnData2 = [75, 25, 65, 50];
					columnData3 = [85, 30, 80, 40];
					break;
				default:
					columnData = [44, 55, 57, 56];
					columnData2 = [76, 85, 101, 98];
					columnData3 = [35, 41, 36, 26];
			}
			summaryChart.updateSeries([
				{
					name: "Net Profit",
					data: columnData
				},{
					name: 'Revenue',
					data: columnData2
				},{
					name: 'Free Cash Flow',
					data: columnData3
				}
			]);
		})
	}

	var revenueChart = function(){
		var options = {
          series: [{
          name: 'series1',
          data: [10, 20, 30, 40]
        }, 
		{
          name: 'series2',
          data: [12, 15, 25, 30]
        }],
          chart: {
          type: 'area',
          height: 200,
			toolbar: {
				show: false,
			},
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
			curve: 'smooth',
			width:3
        },
		colors:["#FFAB2D","#00ADA3"],
		dataLabels: {
          enabled: false
        },
		grid: {
			show: true,
			borderColor: '#eee',
			strokeDashArray: 0,
			position: 'back',
			xaxis: {
				lines: {
					show: false
				}
			},  
		},
        xaxis: {
			categories: ['Jan', 'Fab', 'Mar', 'Apr'],
			labels: {
				show: true,
				minHeight: 25,
				maxHeight: 0,
				style: {
					colors: 'var(--title)',
					fontSize: '12px',
					fontFamily: 'poppins',
					fontWeight: 100,
					cssClass: 'apexcharts-xaxis-label',
				},
			},
			axisTicks: {
				show: false,
			},
			crosshairs: {
				show: false,
			},
		},
		yaxis: {
			labels: {
			show: true,
				offsetX:-5,
				style: {
					colors: 'var(--title)',
					fontSize: '12px',
					fontFamily: 'poppins',
					fontWeight: 100,
					cssClass: 'apexcharts-xaxis-label',
				},
				formatter: function (value) {
				  return value + "k";
				}
			},
		},
		legend: {
			show: false,
		},
        tooltip: {
          x: {
            format: 'dd/MM/yy HH:mm'
          },
        },
        };

        var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), options);
        revenueChart.render();
		
		$(".revenue-chart .nav-link").on('click',function(){
			var seriesType = $(this).attr('data-series');
			var columnData = [];
			var columnData2 = [];
			switch(seriesType) {
				case "week":
					columnData = [20, 25, 35, 40];
					columnData2 = [5, 10, 30, 20];
					break;
				case "month":
					columnData = [10, 15, 25, 30];
					columnData2 = [5, 10, 30, 20];
					break;
				case "today":
					columnData = [15, 20, 10, 25];
					columnData2 = [10, 15, 20, 30];
					break;
				default:
					columnData = [10, 20, 30, 40];
					columnData2 = [10, 20, 30, 40];
			}
			revenueChart.updateSeries([
				{
					name: "series1",
					data: columnData
				},{
					name: 'series2',
					data: columnData2
				}
			]);
		})
	}
	
	/* Function ============ */
		return {
			init:function(){
    		},
			
			load:function(){
       			projectChart();
       			earningChart();
       			summaryChart();
       			revenueChart();
			},
			
			resize:function(){
				
			}
		}
	
	}();

	jQuery(document).ready(function(){
	});
		
	jQuery(window).on('load',function(){
		setTimeout(function(){
			dzChartlist.load();
		}, 1000); 
		
	});

	jQuery(window).on('resize',function(){
		
		
	});     

})(jQuery);