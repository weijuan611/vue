<template>
    <page-wrap>

        <div class="card-wrap card-wrap-1" v-if="buttonDisable.workbenchindex">
            <el-card>
                <div slot="header" class="clearfix">
                  <b><i class="el-icon-tickets"></i>&nbsp;&nbsp;流量概况</b>
                </div>

                <el-table
                    :data="TableData"
                    border
                    max-height="300"
                    size="mini">
                    <el-table-column label="日期"  width="110px">
                      <template slot-scope="scope">
                        <a v-show="scope.row.create_time !== '总计'" class="a_button" @click="clickliuliang(scope.row.create_time)">{{scope.row.create_time}}</a>
                          <span v-show="scope.row.create_time === '总计'">总计</span>
                      </template>
                    </el-table-column>
                    <el-table-column  label="浏览次数(PV)">
                        <el-table-column show-overflow-tooltip width="65px" label="PC端" prop="pvPC"></el-table-column>
                        <el-table-column show-overflow-tooltip width="65px" label="M端" prop="pvM"></el-table-column>
                        <el-table-column show-overflow-tooltip width="65px" label="合计" prop="pvTotal"></el-table-column>
                    </el-table-column>
                    <el-table-column label="独立访客(UV)">
                        <el-table-column show-overflow-tooltip width="65px" label="PC端" prop="uvPC"></el-table-column>
                        <el-table-column show-overflow-tooltip width="65px" label="M端" prop="uvM"></el-table-column>
                        <el-table-column show-overflow-tooltip width="65px" label="合计" prop="uvTotal"></el-table-column>
                    </el-table-column>
                    <el-table-column label="跳出率">
                        <el-table-column show-overflow-tooltip width="70px" label="PC端" prop="jumpPC"></el-table-column>
                        <el-table-column show-overflow-tooltip width="70px" label="M端" prop="jumpM"></el-table-column>
                        <el-table-column show-overflow-tooltip width="70px" label="合计" prop="jumpTotal"></el-table-column>
                    </el-table-column>
                    <el-table-column show-overflow-tooltip width="65px" label="原始单" prop="sum"></el-table-column>
                    <el-table-column show-overflow-tooltip width="65px" label="订单" prop="order_sum_alone"></el-table-column>
                    <el-table-column show-overflow-tooltip width="70px" label="转化率" prop="order_rate"></el-table-column>
                    <el-table-column label="关键词top10">
                        <el-table-column show-overflow-tooltip width="65px" label="PC端" prop="top10PC"></el-table-column>
                        <el-table-column show-overflow-tooltip width="65px" label="M端" prop="top10M"></el-table-column>
                        <el-table-column show-overflow-tooltip width="65px" label="合计" prop="top10Total"></el-table-column>
                    </el-table-column>
                    <el-table-column label="关键词top50">
                        <el-table-column show-overflow-tooltip width="65px" label="PC端" prop="top50PC"></el-table-column>
                        <el-table-column show-overflow-tooltip width="65px" label="M端" prop="top50M"></el-table-column>
                        <el-table-column show-overflow-tooltip width="65px" label="合计" prop="top50Total"></el-table-column>
                    </el-table-column>
                </el-table>

                <div id="LLLineChart" class="chart"></div>
            </el-card>

        </div>

        <div v-else style="width:99%;margin-top:300px;text-align:center;">
            <el-tag type="warning"><i class="el-icon-warning" style="margin-right:10px;"></i>您无权限查看工作台</el-tag>
        </div>

    </page-wrap>
</template>

<script>
    import PageWrap from './../components/pageStructure/PageWrap.vue'
    import SearchWrap from './../components/pageStructure/SearchWrap.vue'
    import OtherWrap from './../components/pageStructure/OtherWrap.vue'
    import TableWrap from './../components/pageStructure/TableWrap.vue'
    import PageTitle from './../components/pageStructure/PageTitle.vue'
    import Rectangle from './../components/pageStructure/Rectangle.vue'

    import {getDates} from './../assets/js/baseFunc/baseSelfFunc'

    import {mapState, mapMutations} from 'vuex'

    export default {
        components: {
            PageWrap, SearchWrap,OtherWrap,TableWrap,PageTitle,Rectangle
        },
        props: {
            freshTab: String
        },
        data() {
            return {
                TableData: [],
                buttonDisable: {
                    workbenchindex: true
                },
                lineChartData: [],
                xAxis: [],
                chartLegend: [],
            }
        },
        watch: {
            freshTab() {
                if (this.freshTab === 'Home') {
                    this.init()
                    this.freshTabFunc('')
                }
            }
        },
        created() {
            this.init()
        },
        mounted() {
            this.createChart()
        },
        methods: {
            init() {
                let that = this
                this.$ajax.get('/workbench/index')
                    .then(function (res) {
                        that.buttonDisable.workbenchindex = res.data.buttonDisable.workbenchindex;
                        that.TableData = res.data.LLLineChart.table;
                        that.lineChartData = res.data.LLLineChart.chart.data;
                        that.xAxis = res.data.LLLineChart.chart.xAxis;
                        that.createChart()
                    })
            },
            createChart() {
                let lineChart_option  = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'cross',
                            crossStyle: {
                                color: '#999'
                            }
                        }
                    },
                    toolbox: {
                        feature: {
                            dataView: {show: false, readOnly: true},
                            magicType: {show: true, type: ['line', 'bar']},
                            restore: {show: true},
                            saveAsImage: {show: true}
                        }
                    },
                    legend: {
                        data:['PC端UV','M端UV','原始单','有效单']
                    },
                    xAxis: [
                        {
                            type: 'category',
                            data: this.xAxis,
                            axisPointer: {
                                type: 'shadow'
                            }
                        }
                    ],
                    yAxis: [
                        {
                            type: 'value',
                            name: '访问数',
                            axisLabel: {
                                formatter: '{value}'
                            }
                        },
                        {
                            type: 'value',
                            name: '单数',
                            axisLabel: {
                                formatter: '{value}'
                            }
                        }
                    ],
                    series: this.lineChartData
                };
                let lineChart = this.$echarts.init(document.getElementById('LLLineChart'))
                lineChart.setOption(lineChart_option)
            },
            clickliuliang (date) {
                this.addTab({name: 'flowAnalysis/TrendAnalysis', title: '趋势分析与对比'})
                this.func_domain_to_trendAnalysis({'date': date.substr(0,10)})
            },
            ...mapMutations('navTabs', [   // 下拉框选择所有已打开的标签
                'freshTabFunc','addTab', 'func_domain_to_trendAnalysis'
            ])
        },
    }
</script>

<style scoped>
    @import "./../assets/css/page/home.css";
    .el-card {
      width: 99%;
    }
    .el-card .el-table {
      width: 100%;
    }
    .el-card .chart {
      width: 96%;
      height: 200px;
      margin: 15px auto;
    }
    .a_button {
      color: #4c969e;
      cursor: pointer;
      float: right;
      margin-right: 5px;
    }
</style>
