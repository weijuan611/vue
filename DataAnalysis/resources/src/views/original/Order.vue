<template>
  <page-wrap>

  	<page-title title="原始单与订单-订单概况" :date="searchData.dateTime" :date2="searchData.compare ? searchData.dateTime2 : null" />

    <search-wrap>
      <el-form ref="form" :model="searchData" size="mini">
        <el-form-item>
          <el-radio-group v-model="dayMark" @change="changeDayMark">
            <el-radio-button label="昨天"></el-radio-button>
            <el-radio-button label="前天"></el-radio-button>
            <el-radio-button label="最近7天"></el-radio-button>
          </el-radio-group>
          <el-date-picker
                  v-model="searchData.dateTime"
                  type="daterange"
                  value-format="yyyy-MM-dd"
                  @change="changeDate"
                  range-separator="至"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期">
          </el-date-picker>
          <el-checkbox v-model="searchData.compare" @change="changeCompare">对比</el-checkbox>
          <el-date-picker
                  v-model="searchData.dateTime2"
                  v-show="showCompareDate"
                  type="daterange"
                  value-format="yyyy-MM-dd"
                  @change="changeDate2"
                  range-separator="至"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期">
          </el-date-picker>
        </el-form-item>
      </el-form>
    </search-wrap>

    <other-wrap>
      <rectangle :show="!searchData.compare" :data="order1" cols="8" />

      <rectangle :show="searchData.compare" :data="order1" cols="8" words="时段一" iconType="colorBall2" iconBg="#42d4be" iconWidth="100px" />
      <rectangle :show="searchData.compare" :data="order2" cols="8" words="时段二" iconType="colorBall2" iconBg="#42d4be" iconWidth="100px" />

      <el-card class="line-chart">
        <div class="box-card-content">
          <div id="order_pieChart1"></div>
          <div id="order_pieChart2" v-show="searchData.compare"></div>
          <div id="order_lineChart" v-show="searchData.compare === false"></div>
        </div>
      </el-card>
    </other-wrap>

    <!-- 表格 -->
    <table-wrap>
      <el-table
        v-loading="loading1"
        v-show="!searchData.compare"
        :data="tableData.simple"
        border
        :row-class-name="tableRowClassName"
        size="mini">
        <el-table-column label="日期" prop="datetime"></el-table-column>
        <el-table-column label="原始单总量" prop="sum"></el-table-column>
        <el-table-column label="订单总量" prop="order_sum"></el-table-column>
        <el-table-column label="订单总量（去重）" prop="order_sum_alone"></el-table-column>
        <el-table-column label="原始单-订单转化率" prop="order_rate"></el-table-column>
        <el-table-column label="订单-在线坐席（去重）" prop="seat_num" width="160px"></el-table-column>
        <el-table-column label="订单-在线报名（去重）" prop="sign_num" width="160px"></el-table-column>
        <el-table-column label="订单-400电话（去重）" prop="tel_num" width="160px"></el-table-column>
        <el-table-column label="订单-其他（去重）" prop="other_num" width="160px"></el-table-column>
      </el-table>

      <el-table
        v-show="searchData.compare"
        v-loading="loading2"
        :data="tableData.complex"
        border
        :cell-style="setCellStyle"
        :row-class-name="tableRowClassName"
        max-height="550"
        size="mini">
        <el-table-column
          label="时段"
          width="120px"
          prop="datetime">
        </el-table-column>
        <el-table-column label="原始单总量">
          <el-table-column label="时段一" prop="sum.one"></el-table-column>
          <el-table-column label="时段二" prop="sum.two"></el-table-column>
          <el-table-column label="变化情况" prop="sum.change" width="120px"></el-table-column>
        </el-table-column>
        <el-table-column label="订单总量">
          <el-table-column label="时段一" prop="order_sum.one"></el-table-column>
          <el-table-column label="时段二" prop="order_sum.two"></el-table-column>
          <el-table-column label="变化情况" prop="order_sum.change" width="120px"></el-table-column>
        </el-table-column>
        <el-table-column label="订单总量（去重）">
          <el-table-column label="时段一" prop="order_sum_alone.one"></el-table-column>
          <el-table-column label="时段二" prop="order_sum_alone.two"></el-table-column>
          <el-table-column label="变化情况" prop="order_sum_alone.change" width="120px"></el-table-column>
        </el-table-column>
        <el-table-column label="原始单-订单转化率">
          <el-table-column label="时段一" prop="order_rate.one"></el-table-column>
          <el-table-column label="时段二" prop="order_rate.two"></el-table-column>
          <el-table-column label="变化情况" prop="order_rate.change" width="120px"></el-table-column>
        </el-table-column>
        <el-table-column label="订单-在线坐席（去重）">
          <el-table-column label="时段一" prop="seat_num.one"></el-table-column>
          <el-table-column label="时段二" prop="seat_num.two"></el-table-column>
          <el-table-column label="变化情况" prop="seat_num.change" width="120px"></el-table-column>
        </el-table-column>
        <el-table-column label="订单-在线报名（去重）">
          <el-table-column label="时段一" prop="sign_num.one"></el-table-column>
          <el-table-column label="时段二" prop="sign_num.two"></el-table-column>
          <el-table-column label="变化情况" prop="sign_num.change" width="120px"></el-table-column>
        </el-table-column>
        <el-table-column label="订单-400电话（去重）">
          <el-table-column label="时段一" prop="tel_num.one"></el-table-column>
          <el-table-column label="时段二" prop="tel_num.two"></el-table-column>
          <el-table-column label="变化情况" prop="tel_num.change" width="120px"></el-table-column>
        </el-table-column>
        <el-table-column label="订单-其他（去重）">
          <el-table-column label="时段一" prop="other_num.one"></el-table-column>
          <el-table-column label="时段二" prop="other_num.two"></el-table-column>
          <el-table-column label="变化情况" prop="other_num.change" width="120px"></el-table-column>
        </el-table-column>
      </el-table>
    </table-wrap>

  </page-wrap>
</template>

<script>
    import PageWrap from './../../components/pageStructure/PageWrap.vue'
    import SearchWrap from './../../components/pageStructure/SearchWrap.vue'
    import OtherWrap from './../../components/pageStructure/OtherWrap.vue'
    import TableWrap from './../../components/pageStructure/TableWrap.vue'

    import PageTitle from './../../components/pageStructure/PageTitle.vue'
    import Rectangle from './../../components/pageStructure/Rectangle.vue'

    import { getDates }  from './../../assets/js/baseFunc/baseSelfFunc'

    export default {
        components: {
            PageWrap, SearchWrap, OtherWrap, TableWrap, PageTitle, Rectangle
        },
        data () {
            return {
                searchData: {
                    dateTime: [getDates('sevenDay'), getDates('yesterday')],
                    dateTime2: [getDates('fourteenDay'), getDates('eightDay')],
                    compare: false
                },
                order1: [
                    {
                        title: '原始单总量',
                        number: 0
                    },
                    {
                        title: '订单总量',
                        number: 0
                    },
                    {
                        title: '订单总量（去重）',
                        number: 0
                    },
                    {
                        title: '原始单-订单转化率',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-在线坐席（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-在线报名（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-400电话（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-其他（去重）',
                        number: '0(0%)'
                    },
                ],
                order2: [
                    {
                        title: '原始单总量',
                        number: 0
                    },
                    {
                        title: '订单总量',
                        number: 0
                    },
                    {
                        title: '订单总量（去重）',
                        number: 0
                    },
                    {
                        title: '原始单-订单转化率',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-在线坐席（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-在线报名（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-400电话（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-其他（去重）',
                        number: '0(0%)'
                    },
                ],
                order_lineChartData: {
                    left: {
                        pv: [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                        uv: [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                        ip: [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                    },
                    right: {
                        pv: [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                        uv: [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                        ip: [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                    }
                },
                xData: [],
                tableData: {
                    simple: [],
                    complex: []
                },
                loading1: false,
                loading2: false,
                dayMark: '最近7天',
                showCompareDate: false,
                showEcharts: true,
                chartLegend: [],
                chartSeries: [],
                order_pieChart1_Data: [
                  {
                    value: 0,
                    name: '订单-在线坐席: 0(0%)'
                  },
                  {
                    value: 0,
                    name: '订单-在线报名: 0(0%)'
                  },
                  {
                    value: 0,
                    name: '订单-400电话: 0(0%)'
                  },
                  {
                    value: 0,
                    name: '订单-其它: 0(0%)'
                  }
                ],
                order_pieChart2_Data: [
                    {
                        value: 0,
                        name: '订单-在线坐席: 0(0%)'
                    },
                    {
                        value: 0,
                        name: '订单-在线报名: 0(0%)'
                    },
                    {
                        value: 0,
                        name: '订单-400电话: 0(0%)'
                    },
                    {
                        value: 0,
                        name: '订单-其它: 0(0%)'
                    }
                ]
            }
        },
        mounted () {
            this.createChart()
            this.onSubmit()
        },
        methods: {
            changeDayMark (val) {
                switch (val) {
                    case '前天':
                        this.searchData.dateTime = [getDates('daybefore'), getDates('daybefore')]
                        this.searchData.dateTime2 = [getDates('fourDay'), getDates('fourDay')]
                        break
                    case '昨天':
                        this.searchData.dateTime = [getDates('yesterday'), getDates('yesterday')]
                        this.searchData.dateTime2 = [getDates('daybefore'), getDates('daybefore')]
                        break
                    case '最近7天':
                        this.searchData.dateTime = [getDates('sevenDay'), getDates('yesterday')]
                        this.searchData.dateTime2 = [getDates('fourteenDay'), getDates('eightDay')]
                        break
                }
                this.isshowEchart()
                this.onSubmit()
            },
            changeDate (val) {
                if (val[0] === getDates('daybefore') && val[1] === getDates('daybefore')) {
                    this.dayMark = '前天'
                    this.searchData.dateTime2 = [getDates('fourDay'), getDates('fourDay')]
                } else if (val[0] === getDates('yesterday') && val[1] === getDates('yesterday')) {
                    this.dayMark = '昨天'
                    this.searchData.dateTime2 = [getDates('daybefore'), getDates('daybefore')]
                } else if (val[0] === getDates('sevenday') && val[1] === getDates('yesterday')) {
                    this.dayMark = '最近7天'
                    this.searchData.dateTime2 = [getDates('fourteenDay'), getDates('eightDay')]
                } else {
                    this.dayMark = ''
                }
                this.isshowEchart()
                this.onSubmit()
            },
            changeDate2 (val) {
                this.isshowEchart()
                this.onSubmit()
            },
            changeCompare (val) {
                this.showCompareDate = val
                this.isshowEchart()
                this.createChart()
                // this.onSubmit()
                this.order1 = [
                    {
                        title: '原始单总量',
                        number: 0
                    },
                    {
                        title: '订单总量',
                        number: 0
                    },
                    {
                        title: '订单总量（去重）',
                        number: 0
                    },
                    {
                        title: '原始单-订单转化率',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-在线坐席（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-在线报名（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-400电话（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-其他（去重）',
                        number: '0(0%)'
                    },
                ]
                this.order2 = [
                    {
                        title: '原始单总量',
                        number: 0
                    },
                    {
                        title: '订单总量',
                        number: 0
                    },
                    {
                        title: '订单总量（去重）',
                        number: 0
                    },
                    {
                        title: '原始单-订单转化率',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-在线坐席（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-在线报名（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-400电话（去重）',
                        number: '0(0%)'
                    },
                    {
                        title: '订单-其他（去重）',
                        number: '0(0%)'
                    },
                ]
            },
            tableRowClassName({row, rowIndex}) {
                if (rowIndex === 0) {
                    return 'sumClass'
                }
                return ''
            },
            onSubmit() {
                let that = this
                that.loading1 = true
                that.loading2 = true
                this.$ajax.post('/original_order/index', that.searchData)
                    .then(function (res) {
                        if (res.data !== undefined) {
                            if (that.searchData.compare === false) {
                                if (that.searchData.dateTime[0] === that.searchData.dateTime[1] && that.searchData.dateTime2[0] === that.searchData.dateTime2[1]) {
                                    that.tableData.simple = res.data.tableData
                                    that.order1 = res.data.order1
                                    that.order2 = res.data.order2
                                    that.order_pieChart1_Data = res.data.pieChartData
                                    that.createChart()
                                } else {
                                    that.xData = res.data.xdata
                                    that.tableData.simple = res.data.tableData
                                    that.order1 = res.data.order1
                                    that.chartSeries = res.data.lineChartData
                                    that.order_pieChart1_Data = res.data.pieChartData
                                    that.createChart()
                                }
                            } else {
                                that.tableData.complex = res.data.tableData
                                that.order1 = res.data.order1
                                that.order2 = res.data.order2
                                that.order_pieChart1_Data = res.data.pieChartData1
                                that.order_pieChart2_Data = res.data.pieChartData2
                                that.createChart()
                            }
                        }
                        that.loading1 = false
                        that.loading2 = false
                    })
                    .catch(function(err) {

                    })
            },
            setFontColor (val, cells) {
              for (let i = 0; i < cells.length; i++) {
                if (val.columnIndex === cells[i].index) {
                  if (val.row[cells[i].name].type === 'up') {
                    return { color : 'red', fontWeight: 'bold' }
                  } else if (val.row[cells[i].name].type === 'down') {
                    return { color : 'green', fontWeight: 'bold' }
                  } else {
                    return { color : '#777', fontWeight: 'bold' }
                  }
                }
              }
            },
            setCellStyle (val) {
                let cells = [
                  {
                    index: 3,
                    name: 'originalList'
                  },
                  {
                    index: 6,
                    name: 'uv'
                  },
                  {
                    index: 9,
                    name: 'validList'
                  },
                  {
                    index: 12,
                    name: 'orderConversion'
                  }
                ]
                return this.setFontColor(val, cells)
            },
            createChart () {
              let order_pieChart1_option = {
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b}"
                },
                legend: {
                    orient: 'vertical',
                    x: 'left',
                    data: this.order_pieChart1_Data.map(x => x.name)
                },
                color:['#0980d9', '#1d9bf9', '#4db3ff', '#79c3fa', '#cee7fa', '#c6e5fc'],
                series : [
                    {
                        name: '时段一',
                        type: 'pie',
                        radius: ['30%', '60%'],
                        center: ['58%', '65%'],
                        data: this.order_pieChart1_Data,
                        label: {
                            normal: {
                                show: false,
                                position: 'center'
                            }
                        }
                    }
                ]
              }
              let order_pieChart2_option = {
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b}"
                },
                legend: {
                    orient: 'vertical',
                    x: 'left',
                    data: this.order_pieChart2_Data.map(x => x.name)
                },
                color:['#ee2d2d', '#fb5050', '#f56c6c', '#f89090', '#fababa', '#fad9d9', '#fce4e4'],
                series : [
                    {
                        name: '时段一',
                        type: 'pie',
                        radius: ['30%', '60%'],
                        center: ['58%', '65%'],
                        data: this.order_pieChart2_Data,
                        label: {
                            normal: {
                                show: false,
                                position: 'center'
                            }
                        }
                    }
                ]
              }
              this.$echarts.init(document.getElementById('order_pieChart1')).setOption(order_pieChart1_option)
              this.$echarts.init(document.getElementById('order_pieChart2')).setOption(order_pieChart2_option)

                this.chartLegend = !this.showCompareDate ? ['UV', '原始单', '有效原始单'] : ['时段一UV', '时段二UV', '时段一原始单', '时段二原始单', '时段一有效原始单', '时段二有效原始单']
                let colors = ['#5793f3', '#d14a61', 'DarkTurquoise', 'OrangeRed', 'RoyalBlue', 'DeepPink']
                let order_lineChart_option = {
                   color: colors,
                   tooltip: {
                       trigger: 'axis',
                       axisPointer: {
                           type: 'cross'
                       }
                   },
                   grid: {
                       right: '15%'
                   },
                   legend: {
                       data: this.chartLegend
                   },
                   xAxis: [
                       {
                           type: 'category',
                           axisTick: {
                               alignWithLabel: true
                           },
                           data: this.xData
                       }
                   ],
                   yAxis: [
                       {
                           type: 'value',
                           position: 'left',
                           axisLine: {
                               lineStyle: {
                                   color: colors[0]
                               }
                           }
                       },
                       {
                           type: 'value',
                           position: 'right',
                           offset: 10,
                           axisLine: {
                               lineStyle: {
                                   color: colors[1]
                               }
                           }
                       }
                   ],
                   series: this.chartSeries
                }
                let that = this
                let order_lineChart = this.$echarts.init(document.getElementById('order_lineChart'))
                order_lineChart.setOption(order_lineChart_option)
                order_lineChart.on('legendselectchanged', function (params) {
                    if (that.searchData.compare) {
                        switch (params.name) {
                            case '时段一UV':
                                params.selected['时段二UV'] = params.selected[params.name]
                                break
                            case '时段二UV':
                                params.selected['时段一UV'] = params.selected[params.name]
                                break
                            case '时段一原始单':
                                params.selected['时段二原始单'] = params.selected[params.name]
                                break
                            case '时段二原始单':
                                params.selected['时段一原始单'] = params.selected[params.name]
                                break
                            case '时段一有效原始单':
                                params.selected['时段二有效原始单'] = params.selected[params.name]
                                break
                            case '时段二有效原始单':
                                params.selected['时段一有效原始单'] = params.selected[params.name]
                                break
                        }
                        order_lineChart_option.legend.selected = params.selected
                        order_lineChart.setOption(order_lineChart_option)
                    }
                })
            },
            isshowEchart () {
                if (this.searchData.compare === false) {
                    if (this.searchData.dateTime[0] === this.searchData.dateTime[1] && this.searchData.dateTime2[0] === this.searchData.dateTime2[1]) {
                        this.showEcharts = false
                    } else {
                        this.showEcharts = true
                    }
                } else {
                    this.showEcharts = false
                }
            }
        }
    }
</script>

<style scoped>
  @import "./../../assets/css/page/order.css";
</style>
