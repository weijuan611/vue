<template>
  <page-wrap>

    <div class="title" v-if="buttonDisable.workbenchindex">
      昨日<span class="day">{{yesterday}}</span>网站概况
    </div>

    <div v-else style="width:100%;margin-top:300px;text-align:center;">
      <el-tag type="warning"><i class="el-icon-warning" style="margin-right:10px;"></i>您无权限查看工作台</el-tag>
    </div>

    <div class="rect" v-if="buttonDisable.workbenchindex">
      <template v-for="(item, index) in compareAnalysisData" v-if="index < 5">
        <data-analysis-block
          :key="index"
          :title="item.title"
          :number="item.number"
          :compareDayTrend="item.compareDayTrend"
          :compareWeekTrend="item.compareWeekTrend"
          :compareDay="item.compareDay"
          :compareWeek="item.compareWeek"
        />
      </template>
    </div>

    <div class="rect" v-if="buttonDisable.workbenchindex">
      <template v-for="(item, index) in compareAnalysisData" v-if="index >= 5">
        <data-analysis-block
          :key="index"
          :title="item.title"
          :number="item.number"
          :compareDayTrend="item.compareDayTrend"
          :compareWeekTrend="item.compareWeekTrend"
          :compareDay="item.compareDay"
          :compareWeek="item.compareWeek"
        />
      </template>
    </div>

    <div class="card-wrap card-wrap-1" v-if="buttonDisable.workbenchindex">
      <el-card class="visitor-trend">
      <div class="box-card-head" slot="header">
        <span>访问趋势</span>
        <el-radio-group v-model="visitorTrendData.showPv" size="mini" class="button" @change="visitorTrendChange">
          <el-radio-button label="PV"></el-radio-button>
          <el-radio-button label="UV"></el-radio-button>
        </el-radio-group>
      </div>
      <div class="box-card-content">
        <div id="visitTrend"></div>
      </div>
    </el-card>

      <el-card class="sourse-domain" >
        <div class="box-card-head" slot="header">
          <span>来源域名Top5</span>
        </div>
        <div class="box-card-content">
          <div class="origin">
            <template v-for="item in sourseDomainData">
              <pctRect
                :width="item.number"
                :color="item.color"
                :url="item.url"
                :number="item.number"
              />
            </template>
          </div>
        </div>
      </el-card>
    </div>

    <div class="card-wrap card-wrap-2" style="display: none">
      <el-card class="visit-area-ip">
        <div class="box-card-head" slot="header">
          <span>访客地域(按IP)</span>
        </div>
        <div class="box-card-content">
          <div id="visitAreaIp"></div>
        </div>
      </el-card>

      <el-card class="visit-area-domain">
        <div class="box-card-head" slot="header">
          <span>访客地域(按域名)</span>
        </div>
        <div class="box-card-content">
          <div id="visitAreaDomain"></div>
        </div>
      </el-card>
    </div>

    <div class="card-wrap card-wrap-3" style="display: none">
      <el-card class="visit-dist">
        <div class="box-card-head" slot="header">
          <span>新老访客分布(按独立访客)</span>
        </div>
        <div class="box-card-content">
          <div id="visitDist"></div>
        </div>
      </el-card>

      <el-card class="search-engine">
        <div class="box-card-head" slot="header">
          <span>搜索引擎Top5</span>
        </div>
        <div class="box-card-content">
          <div id="searchEngine"></div>
        </div>
      </el-card>

      <el-card class="key-word">
        <div class="box-card-head" slot="header">
          <span>关键词Top5</span>
        </div>
        <div class="box-card-content">
          <div id="keyWord"></div>
        </div>
      </el-card>
    </div>

    <el-card class="card-wrap-4" style="display: none">
      <div class="box-card-head" slot="header">
        <el-radio-group v-model="day" size="mini">
          <el-radio-button label="今日"></el-radio-button>
          <el-radio-button label="昨日"></el-radio-button>
        </el-radio-group>
      </div>
      <div class="box-card-content">
        <el-card class="search-word-table">
          <div class="box-card-head" slot="header">
            <span>来路域名Top10(按来访次数)</span>
            <div class="button">
              <i class="el-icon-tickets" title="查看全部"></i>
              <i class="el-icon-close" title="关闭"></i>
            </div>
          </div>
          <div class="box-card-content">
            <el-table
              height="250"
              :data="routeDomainData">
              <el-table-column
                prop="from"
                width="180px"
                label="来源">
              </el-table-column>
              <el-table-column
                prop="number"
                label="来访次数">
              </el-table-column>
              <el-table-column
                prop="percent"
                label="占比">
              </el-table-column>
            </el-table>
          </div>
        </el-card>

        <el-card class="visitor-dist-table">
          <div class="box-card-head" slot="header">
            <span>搜索词Top10(按来访次数)</span>
            <div class="button">
              <i class="el-icon-tickets" title="查看全部"></i>
              <i class="el-icon-close" title="关闭"></i>
            </div>
          </div>
          <div class="box-card-content">
            <el-table
              height="250"
              :data="searchWordData">
              <el-table-column
                prop="searchWord"
                width="150px"
                label="搜索词">
              </el-table-column>
              <el-table-column
                prop="number"
                label="来访次数">
              </el-table-column>
              <el-table-column
                prop="percent"
                label="占比">
              </el-table-column>
            </el-table>
          </div>
        </el-card>

        <el-card class="sourse-domain-table">
          <div class="box-card-head" slot="header">
            <span>新老访客分布(按独立访客)</span>
            <div class="button">
              <i class="el-icon-tickets" title="查看全部"></i>
              <i class="el-icon-close" title="关闭"></i>
            </div>
          </div>
          <div class="box-card-content">
            <div id="sourseDomainTable"></div>
          </div>
        </el-card>

      </div>
    </el-card>

  </page-wrap>
</template>

<script>
  import PageWrap from './../components/pageStructure/PageWrap.vue'
  import DataAnalysisBlock from './../components/base/DataAnalysisBlock.vue'
  import PctRect from './../components/base/PctRect.vue'
  import { getDates }  from './../assets/js/baseFunc/baseSelfFunc'

  import { mapState, mapMutations } from 'vuex'

  export default {
    components: {
      PageWrap, DataAnalysisBlock, PctRect
    },
    props: {
      freshTab: String
    },
    data () {
      return {
        yesterday: getDates('yesterday'),
        day: '今日',
        buttonDisable: {
          workbenchindex: true
        },
        compareAnalysisData: [
          {
            title: '昨日浏览次数( PV )',
            number: 0,
            compareDayTrend: 'down',
            compareWeekTrend: 'up',
            compareDay: '00.00%',
            compareWeek: '00.00%'
          },
          {
            title: '独立访客( UV )',
            number: 0,
            compareDayTrend: 'up',
            compareWeekTrend: 'down',
            compareDay: '00.00%',
            compareWeek: '00.00%'
          },
          {
            title: 'IP',
            number: 0,
            compareDayTrend: 'up',
            compareWeekTrend: 'down',
            compareDay: '00.00%',
            compareWeek: '00.00%'
          },
          {
            title: '新独立访客',
            number: 0,
            compareDayTrend: 'up',
            compareWeekTrend: 'down',
            compareDay: '00.00%',
            compareWeek: '00.00%'
          },
          {
            title: '访问次数',
            number: 0,
            compareDayTrend: 'up',
            compareWeekTrend: 'down',
            compareDay: '00.00%',
            compareWeek: '00.00%'
          },
          {
            title: '访客平均访问频度',
            number: 0,
            compareDayTrend: 'up',
            compareWeekTrend: 'down',
            compareDay: '00.00%',
            compareWeek: '00.00%'
          },
          {
            title: '平均访问时长(秒)',
            number: 0,
            compareDayTrend: 'up',
            compareWeekTrend: 'down',
            compareDay: '00.00%',
            compareWeek: '00.00%'
          },
          {
            title: '平均访问深度',
            number: 0,
            compareDayTrend: 'up',
            compareWeekTrend: 'down',
            compareDay: '00.00%',
            compareWeek: '00.00%'
          },
          {
            title: '人均浏览页数',
            number: 0,
            compareDayTrend: 'up',
            compareWeekTrend: 'down',
            compareDay: '00.00%',
            compareWeek: '00.00%'
          },
          {
            title: '跳出率',
            number: 0,
            compareDayTrend: 'up',
            compareWeekTrend: 'down',
            compareDay: '00.00%',
            compareWeek: '00.00%'
          }
        ],

        visitorTrendData: {
          showPv: 'PV',
          yesterdayDataPV: [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
          todayDataPV: [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
          yesterdayDataUV: [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
          todayDataUV: [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
        },

        sourseDomainData: [
          {
            color: '#409eff',
            url: '',
            number: 0
          },
          {
            color: '#67c23a',
            url: '',
            number: 0
          },
          {
            color: '#f56c6c',
            url: '',
            number: 0
          },
          {
            color: 'orange',
            url: '',
            number: 0
          },
          {
            color: '#2ec7c9',
            url: '',
            number: 0
          }
        ],

        visitAreaIpData: [
          {
            name: '广东',
            value: 110,
            itemStyle: {
              normal: {
                  color: '#2ec7c9',
                  label: {
                      show: true,
                      textStyle: {
                          color: '#000',
                          fontSize: 10
                      }
                  }
              }
            }
           },
           {
             name: '江苏',
             value: 111,
             itemStyle: {
               normal: {
                   color: '#2ec7c9',
                   label: {
                       show: true,
                       textStyle: {
                           color: '#000',
                           fontSize: 10
                       }
                   }
               }
             }
           }
        ],

        visitAreaDomainData: [
          {
            name: '广东',
            value: 110,
            itemStyle: {
              normal: {
                  color: 'orange',
                  label: {
                      show: true,
                      textStyle: {
                          color: '#000',
                          fontSize: 10
                      }
                  }
              }
            }
           },
           {
             name: '内蒙古',
             value: 111,
             itemStyle: {
               normal: {
                   color: 'orange',
                   label: {
                       show: true,
                       textStyle: {
                           color: '#000',
                           fontSize: 10
                       }
                   }
               }
             }
           }
        ],

        visitDistData: [
          {
            value:335,
            name:'直接访问'
          },
          {
            value:310,
            name:'外部链接'
          },
          {
            value:1548,
            name:'搜索引擎'
          }
        ],

        searchEngineData: [
          {value:335, name:'直接访问'},
          {value:310, name:'外部链接'},
          {value:1548, name:'搜索引擎'}
        ],

        keyWordData: ['微软必应', '百度', '搜狗'],

        sourseDomainTableData: [
          {
            value:335,
            name:'老访客'
          },
          {
            value:310,
            name:'新访客'
          }
        ],

        routeDomainData: [
          {
             from: 'www.houxue.com',
             number: '22382',
             percent: '40%'
          },
          {
             from: 'www.houxue.com',
             number: '22382',
             percent: '40%'
          },
          {
             from: 'www.houxue.com',
             number: '22382',
             percent: '40%'
          },
          {
             from: 'www.houxue.com',
             number: '22382',
             percent: '40%'
          },
          {
             from: 'www.houxue.com',
             number: '22382',
             percent: '40%'
          },
          {
             from: 'www.houxue.com',
             number: '22382',
             percent: '40%'
          },
          {
             from: 'www.houxue.com',
             number: '22382',
             percent: '40%'
          },
           {
              from: 'www.houxue.com',
              number: '22382',
              percent: '40%'
            },
            {
               from: 'www.houxue.com',
               number: '22382',
               percent: '40%'
             },
             {
                from: 'www.houxue.com',
                number: '22382',
                percent: '40%'
              }
        ],

        searchWordData: [
          {
             searchWord: '厚学网',
             number: '22382',
             percent: '40%'
          },
          {
              searchWord: '厚学网',
              number: '22382',
              percent: '40%'
          },
          {
               searchWord: '厚学网',
               number: '22382',
               percent: '40%'
          },
          {
               searchWord: '厚学网',
               number: '22382',
               percent: '40%'
          },
          {
               searchWord: '厚学网',
               number: '22382',
               percent: '40%'
          },
          {
               searchWord: '厚学网',
               number: '22382',
               percent: '40%'
          },
          {
               searchWord: '厚学网',
               number: '22382',
               percent: '40%'
          },
          {
               searchWord: '厚学网',
               number: '22382',
               percent: '40%'
          },
          {
               searchWord: '厚学网',
               number: '22382',
               percent: '40%'
          },
          {
                searchWord: '厚学网',
                number: '22382',
                percent: '40%'
          }
         ]
      }
    },
    watch: {
      freshTab () {
        if (this.freshTab === 'Home') {
          this.init()
          this.freshTabFunc('')
        }
      }
    },
    created () {
      this.init()
    },
    mounted () {
      let visitTrend_legendData = [
        getDates('daybefore') + '前日(' + this.visitorTrendData.showPv + ')',
        getDates('yesterday') + '昨日(' + this.visitorTrendData.showPv + ')'
      ]
      let visitTrend_option = {
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
        },
        tooltip: {
          trigger: 'axis'
        },
        legend: {
          align: 'left',
            left: 10,
            data: visitTrend_legendData
        },
        xAxis: {
          type: 'category',
          name: 'x',
          splitLine: {show: false},
          boundaryGap: false,
          data: ['00:00', '', '', '', '04:00', '', '', '', '08:00', '', '', '', '12:00', '', '', '',
           '16:00', '', '', '', '20:00', '', '', '', '24:00']
        },
        yAxis: {
          type: 'value'
        },
        series: [
          {
            name: visitTrend_legendData[0],
            type: 'line',
            data: this.visitorTrendData.yesterdayDataPV,
            color: 'blue',
            itemStyle: {
              normal: {
                  color: "#2ec7c9",
                  lineStyle: {
                      color: "#2ec7c9"
                  }
              }
            }
          },
          {
            name: visitTrend_legendData[1],
            type: 'line',
            data: this.visitorTrendData.todayDataPV,
            itemStyle: {
              normal: {
                  color: "orange",
                  lineStyle: {
                      color: "orange"
                  }
              }
            }
          }
        ]
      }

      let visitAreaIp_option = {
        tooltip : {
          trigger: 'item'
        },
        series : [
            {
                name: 'ip',
                type: 'map',
                mapType: 'china',
                selectedMode : 'single',
                itemStyle: {
                 normal: {
                     borderWidth: 1,
                     borderColor: '#fff',
                     color: 'orange'
                 },
                 emphasis: {                 // 也是选中样式
                     borderWidth:2,
                     borderColor:'yellow',
                     color: 'red',
                     label: {
                         show: true,
                         textStyle: {
                             color: '#000'
                         }
                     }
                 }
             },
              data: this.visitAreaIpData
            }
        ]
      }
      let visitAreaDomain_option = {
        tooltip : {
          trigger: 'item'
        },
        series : [
            {
                name: 'ip',
                type: 'map',
                mapType: 'china',
                selectedMode : 'single',
                itemStyle: {
                  normal: {
                     borderWidth: 1,
                     borderColor: '#fff',
                     color: 'orange',
                     label: {
                         show: false,
                         textStyle: {
                             color: '#000',
                             fontSize: 10
                         }
                     }
                  },
                  emphasis: {                 // 也是选中样式
                     borderWidth:2,
                     borderColor:'yellow',
                     color: 'red',
                     label: {
                         show: true,
                         textStyle: {
                             color: '#000'
                         }
                     }
                 }
              },
              data: this.visitAreaDomainData
            }
        ]
      }

      let visitDist_option = {
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b}: {c} ({d}%)"
        },
        legend: {
            x : 'center',
            y : 'bottom',
            data:['直接访问','外部链接','搜索引擎']
        },
        color:['#2ec7c9','#f56c6c', 'orange'],
        series: [
            {
                name:'访问来源',
                type:'pie',
                radius: ['50%', '70%'],
                center: ['50%', '40%'],
                avoidLabelOverlap: false,
                label: {
                    normal: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        show: true,
                        textStyle: {
                            fontSize: '16',
                            fontWeight: 'bold'
                        }
                    }
                },
                labelLine: {
                    normal: {
                        show: false
                    }
                },
                data: this.visitDistData
            }
        ]
      }
      let searchEngine_option = {
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b}: {c} ({d}%)"
        },
        color:['#2ec7c9','#f56c6c', 'orange'],
        series: [
            {
                name:'访问来源',
                type:'pie',
                radius: ['50%', '70%'],
                center: ['50%', '50%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                },
                data: this.searchEngineData
            }
        ]
      }
      let keyWord_option = {
        color:['#2ec7c9'],
        toolbox: {
          show: true,
          feature: {
              restore: {},
              saveAsImage: {}
          }
        },
         tooltip : {
             trigger: 'axis',
             axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                 type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
             }
         },
         grid: {
             left: '3%',
             right: '4%',
             bottom: '3%',
             containLabel: true
         },
         xAxis : [
             {
                 type : 'category',
                 data : this.keyWordData,
                 axisTick: {
                     alignWithLabel: true
                 }
             }
         ],
         yAxis : [
             {
                 type : 'value'
             }
         ],
         series : [
             {
                 name:'直接访问',
                 type:'bar',
                 barWidth: '60%',
                 data:[10, 5, 6]
             }
         ]
      }

      let sourseDomainTable_option = {
        tooltip : {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        color:['#2ec7c9', 'orange'],
        series : [
            {
                name: '访问来源',
                type: 'pie',
                radius : '55%',
                center: ['50%', '50%'],
                data: this.sourseDomainTableData,
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ]
      }

      this.$echarts.init(document.getElementById('visitTrend')).setOption(visitTrend_option)
      this.$echarts.init(document.getElementById('visitAreaIp')).setOption(visitAreaIp_option)
      this.$echarts.init(document.getElementById('visitAreaDomain')).setOption(visitAreaDomain_option)
      this.$echarts.init(document.getElementById('visitDist')).setOption(visitDist_option)
      this.$echarts.init(document.getElementById('searchEngine')).setOption(searchEngine_option)
      this.$echarts.init(document.getElementById('keyWord')).setOption(keyWord_option)
      this.$echarts.init(document.getElementById('sourseDomainTable')).setOption(sourseDomainTable_option)

    },
    methods: {
      visitorTrendChange (val) {
        this.visitorTrendData.showPv = val
        let visitTrend_legendData = [
          getDates('daybefore') + '前日(' + this.visitorTrendData.showPv + ')',
          getDates('yesterday') + '昨日(' + this.visitorTrendData.showPv + ')'
        ]

        let visitTrend_option = {
          grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
          },
          tooltip: {
            trigger: 'axis'
          },
          legend: {
            align: 'left',
              left: 10,
              data: visitTrend_legendData
          },
          xAxis: {
              type: 'category',
              name: 'x',
              splitLine: {show: false},
              boundaryGap: false,
              data: ['00:00', '', '', '', '04:00', '', '', '', '08:00', '', '', '', '12:00', '', '', '',
               '16:00', '', '', '', '20:00', '', '', '', '24:00']
          },
          yAxis: {
              type: 'value'
          },
          series: [
              {
                name: visitTrend_legendData[0],
                type: 'line',
                data: (val === 'PV')?this.visitorTrendData.yesterdayDataPV:this.visitorTrendData.yesterdayDataUV,
                color: 'blue',
                itemStyle: {
                  normal: {
                      color: "#2ec7c9",
                      lineStyle: {
                          color: "#2ec7c9"
                      }
                  }
                }
              },
              {
                name: visitTrend_legendData[1],
                type: 'line',
                data: (val === 'PV')?this.visitorTrendData.todayDataPV:this.visitorTrendData.todayDataUV,
                itemStyle: {
                  normal: {
                      color: "orange",
                      lineStyle: {
                          color: "orange"
                      }
                  }
                }
              }
          ]
        }
        this.$echarts.init(document.getElementById('visitTrend')).setOption(visitTrend_option)
      },
      init () {
          let that = this
          this.$ajax.get('/workbench/index')
              .then(function(res) {
                that.compareAnalysisData = res.data.compareAnalysisData;
                that.visitorTrendData = res.data.visitorTrendData;
                that.sourseDomainData = res.data.sourseDomainData;
                that.buttonDisable.workbenchindex = res.data.buttonDisable.workbenchindex;
                that.visitorTrendChange('PV')
              })
              .catch(function(err) {
                  self.$message.error('获取监控面板失败！')
              })
      },
      ...mapMutations('navTabs', [   // 下拉框选择所有已打开的标签
          'freshTabFunc'
      ])
    },
  }
</script>

<style scoped>
  @import "./../assets/css/page/home.css";
</style>
