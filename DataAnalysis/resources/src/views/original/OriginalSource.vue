dimensionData<template>
  <page-wrap>

    <page-title title="原始单与订单-原始单来源分布" :date="searchData.dateTime" :date2="searchData.dateTime2" />

    <other-wrap>
      <div class="rect" style="height: 40px;min-height: 40px;line-height: 28px;">
        <span><i class="el-icon-star-off"></i>我的标签：</span>

        <el-tag
          type="warning"
          size="medium"
          style="margin-right: 10px;cursor:pointer;"
          :key="tag"
          v-for="tag in collectedTagsLabel"
          closable
          :disable-transitions="false"
          @click.native="selectCollectedTag(tag)"
          @close="cancelCollect(tag)">
          {{tag}}
        </el-tag>

      </div>
    </other-wrap>

    <search-wrap>
      <el-form ref="form" :model="searchData" size="mini">
        <el-row :gutter="20">
          <el-col :span="24">
            <div class="bg">
              <el-form-item>
                <QuickTimePicker
                  dates="['昨天', '前天', '最近7天', '最近30天']"
                  :hasCompare="true"
                  :reset="bool"
                  @receiveChange="handleReceiveDateChange" />
                <el-button type="primary" @click="onSubmit">查询</el-button>
                <el-button type="danger" @click="onClear">清空</el-button>
              </el-form-item>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="24">
            <div class="bg">
              <el-form-item label="选择维度：">
                <span>分析原始单 按</span>
                <el-cascader
                 style="width: 250px;"
                 expand-trigger="hover"
                 separator="-"
                 change-on-select
                 :options="dimensionData"
                 v-model="searchData.selectedDimension"
                 @change="handleChangeDimension">
               </el-cascader>

               <div class="mark-star-button">
                 <canvas v-show="!markStarBool" id="starCanvas" @click="onSaveTag" width="22" height="22" style="vertical-align: middle;margin-left: 10px;margin-top:2px;"></canvas>
                 <canvas v-show="markStarBool" id="starCanvasMark" @click="onSaveTag" width="22" height="22" style="vertical-align: middle;margin-left: 10px;margin-top:2px;"></canvas>
                 <label @click="onSaveTag">保存为新标签</label>
               </div>

              </el-form-item>
            </div>
          </el-col>
        </el-row>

      </el-form>

    </search-wrap>

    <div>
      <el-card class="treeView fl">
        <div slot="header" class="clearfix">
          <p class="title">标题：{{this.dimensionTitle}}</p>
          <p class="total">原始单总量 <span>134222</span></p>
        </div>

        <ul class="one clearfix">
          <template v-for="(item, index) in dimensionData_one">
            <li :class="treeState.activeIndex[1] === undefined ? (treeState.activeIndex[0] === undefined ? (index === 0 ? 'active' : 'normal') : 'normal')  : (treeState.activeIndex[1] === index ? 'active' : 'normal')" @click="expandTreeNode($event, 'one', index)">
              <i class="logo">1</i>
              <p class="name">{{item.label}}</p>
              <p class="number">{{item.number}}</p>
              <p class="percent">{{item.percent}}</p>
              <template v-if="searchData.compare">
                <hr style="height:1px;border:none;border-top:1px solid lightblue;"  />
                <p class="number">{{item.compareNumber}}</p>
                <p class="percent">{{item.comparePercent}}</p>
              </template>
              <i :class="treeState.activeIndex[1] === undefined ? (treeState.activeIndex[0] === undefined ? (index === 0 && treeState.show[2] ? 'el-icon-caret-bottom' : 'el-icon-caret-top') : 'el-icon-caret-top')  : (treeState.activeIndex[1] === index && treeState.show[2] ? 'el-icon-caret-bottom' : 'el-icon-caret-top')"></i>
            </li>
          </template>
        </ul>

        <transition name="el-zoom-in-top">
          <ul class="two clearfix transition-box" v-show="treeState.show[2]">
            <template v-for="(item, index) in dimensionData_two">
              <li :class="treeState.activeIndex[2] === undefined ? (index === 0 ? 'active' : 'normal') : (treeState.activeIndex[2] === index ? 'active' : 'normal')" @click="expandTreeNode($event, 'two', index)">
                <i class="logo">2</i>
                <p class="name">{{item.label}}</p>
                <p class="number">{{item.number}}</p>
                <p class="percent">{{item.percent}}</p>
                <template v-if="searchData.compare">
                  <hr style="height:1px;border:none;border-top:1px solid lightblue;"  />
                  <p class="number">{{item.compareNumber}}</p>
                  <p class="percent">{{item.comparePercent}}</p>
                </template>
                <i :class="treeState.activeIndex[2] === undefined ? (index === 0 && treeState.show[3] ? 'el-icon-caret-bottom' : 'el-icon-caret-top') : (treeState.activeIndex[2] === index && treeState.show[3] ? 'el-icon-caret-bottom' : 'el-icon-caret-top')"></i>
              </li>
            </template>
          </ul>
        </transition>

        <transition name="el-zoom-in-top">
          <ul class="three clearfix transition-box" v-show="treeState.show[3]">
            <template v-for="(item, index) in dimensionData_three">
              <li :class="treeState.activeIndex[3] === undefined ? (index === 0 ? 'active' : 'normal') : (treeState.activeIndex[3] === index ? 'active' : 'normal')" @click="expandTreeNode($event, 'three', index)">
                <i class="logo">3</i>
                <p class="name">{{item.label}}</p>
                <p class="number">{{item.number}}</p>
                <p class="percent">{{item.percent}}</p>
                <template v-if="searchData.compare">
                  <hr style="height:1px;border:none;border-top:1px solid lightblue;"  />
                  <p class="number">{{item.compareNumber}}</p>
                  <p class="percent">{{item.comparePercent}}</p>
                </template>
              </li>
            </template>
          </ul>
        </transition>
      </el-card>

      <el-card class="lineChart fl" v-if="!searchData.compare">
        <div id="originalSource_lineChart"></div>
      </el-card>

      <el-card class="tabsTable fl">

        <el-tabs type="border-card" @tab-click="changeTab" v-model="activeTab">
          <template v-for="tab in tabsTableData.tabsLabel">
            <el-tab-pane :label="tab"></el-tab-pane>
          </template>

          <template v-if="searchData.compare">
            <el-table
              key="table1"
              v-show="searchData.compare"
              :data="tabsTableData.tabTable.colData"
              border
              height="500"
              size="mini"
              :row-class-name="tableRowClassName"
              :cell-style="setCellStyle"
              style="width: 100%;">
              <el-table-column
                label="时段"
                width="120px"
                prop="col1">
              </el-table-column>
              <template v-for="(value, key, index) in tabsTableData.tabTable.colData[0]">
                <el-table-column v-if="index !== 0" :label="tabsTableData.tabTable.colName[index]">
                  <el-table-column width="80px" label="时段一">
                    <template slot-scope="scope">
                      <span>{{scope.row[key].one}}</span>
                    </template>
                  </el-table-column>
                  <el-table-column width="80px" label="时段二">
                    <template slot-scope="scope">
                      <span>{{scope.row[key].two}}</span>
                    </template>
                  </el-table-column>
                  <el-table-column width="120px" label="变化情况">
                    <template slot-scope="scope">
                      <span>{{scope.row[key].change}}</span>
                    </template>
                  </el-table-column>
                </el-table-column>
              </template>
            </el-table>
          </template>


          <template v-else>
            <el-table
              key="table2"
              v-show="activeTab === '0'"
              :data="tabsTableData.tabTable.colData"
              height="200"
              style="width: 100%;">
              <el-table-column type="expand">
                <template slot-scope="props" v-if="props.row.expandInfo !== undefined">
                  <el-table :data="props.row.expandInfo.colData" style="width: 100%">
                    <template v-for="(value, key, index) in props.row.expandInfo.colData[0]">
                      <el-table-column
                        :label="props.row.expandInfo.colName[index]"
                        :prop="key">
                      </el-table-column>
                    </template>
                  </el-table>
                </template>
              </el-table-column>

              <template v-for="(value, key, index) in tabsTableData.tabTable.colData[0]">
                <el-table-column
                  v-if="key !== 'expandInfo'"
                  width="150px"
                  :label="tabsTableData.tabTable.colName[index]"
                  :prop="key">
                </el-table-column>
              </template>
            </el-table>

            <el-table
              key="table3"
              v-show="activeTab !== '0'"
              :data="tabsTableData.tabTable.colData"
              height="200"
              style="width: 100%;">
              <template v-for="(value, key, index) in tabsTableData.tabTable.colData[0]">
                <el-table-column
                  width="150px"
                  :label="tabsTableData.tabTable.colName[index]"
                  :prop="key">
                </el-table-column>
              </template>
            </el-table>

          </template>

        </el-tabs>

      </el-card>
    </div>

    <!--</el-card>-->

  </page-wrap>

</template>

<script>
  import PageWrap from './../../components/pageStructure/PageWrap.vue'
  import PageTitle from './../../components/pageStructure/PageTitle.vue'
  import SearchWrap from './../../components/pageStructure/SearchWrap.vue'
  import OtherWrap from './../../components/pageStructure/OtherWrap.vue'
  import TableWrap from './../../components/pageStructure/TableWrap.vue'

  import QuickTimePicker from './../../components/base/QuickTimePicker.vue'

  import { getDates }  from './../../assets/js/baseFunc/baseSelfFunc'

  export default {
      components: {
          PageWrap, SearchWrap, OtherWrap, TableWrap, PageTitle, QuickTimePicker
      },
      data () {
        return {
          searchData: {
              dateTime: [],
              dateTime2: [],
              compare: false,
              selectedDimension: []
          },
          collectedTagsId: [],
          dimensionData: [],

          markStarBool: false,
          treeState: {
            show: [false, true, true, true],
            activeIndex: []
          },
          bool: false,
          dimensionTitle: '',
          tabsTableData: {},
          activeTab: 0
        }
      },
      computed: {
        collectedTagsLabel: function () {
          let labelArr = []
          if (this.collectedTagsId.length > 0) {
            for (let i = 0; i < this.collectedTagsId.length; i++) {
              labelArr.push(this.switchIdtoLabel(this.collectedTagsId[i]))
            }
          }
          return labelArr
        },
        selectedDimensionLabel: function () {
          return this.switchIdtoLabel(this.searchData.selectedDimension)
        },
        dimensionData_one: function () {
          let arr = []
          let index_one = this.treeState.activeIndex[0] !== undefined ? this.treeState.activeIndex[0] : 0
          if (this.dimensionData[index_one].children !== undefined) {
            this.dimensionData[index_one].children.map(m => {
              arr.push({
                value: m.value,
                label: m.label,
                number: m.number,
                percent: m.percent,
                compareNumber: m.compare.number,
                comparePercent: m.compare.percent
              })
            })
          }
          return arr
        },
        dimensionData_two: function () {
          let arr = []
          let index_one = this.treeState.activeIndex[0] !== undefined ? this.treeState.activeIndex[0] : 0
          let index_two = this.treeState.activeIndex[1] !== undefined ? this.treeState.activeIndex[1] : 0
          if (this.dimensionData[index_one].children !== undefined && this.dimensionData[index_one].children[index_two].children !== undefined) {
            this.dimensionData[index_one].children[index_two].children.map(m => {
              arr.push({
                value: m.value,
                label: m.label,
                number: m.number,
                percent: m.percent,
                compareNumber: m.compare.number,
                comparePercent: m.compare.percent
              })
            })
          }
          return arr
        },
        dimensionData_three: function () {
          let arr = []
          let index_one = this.treeState.activeIndex[0] !== undefined ? this.treeState.activeIndex[0] : 0
          let index_two = this.treeState.activeIndex[1] !== undefined ? this.treeState.activeIndex[1] : 0
          let index_three = this.treeState.activeIndex[2] !== undefined ? this.treeState.activeIndex[2] : 0
          if (this.dimensionData[index_one].children !== undefined &&
             this.dimensionData[index_one].children[index_two].children !== undefined &&
              this.dimensionData[index_one].children[index_two].children[index_three].children !== undefined) {
            this.dimensionData[index_one].children[index_two].children[index_three].children.map(m => {
              arr.push({
                value: m.value,
                label: m.label,
                number: m.number,
                percent: m.percent,
                compareNumber: m.compare.number,
                comparePercent: m.compare.percent
              })
            })
          }
          return arr
        }
      },
      created() {
        this.initSearchData()
        this.initTabsTableData()
      },
      mounted () {
        this.drawStar()
        this.createChart()
      },
      updated() {
        //do something after updating vue instance
      },
      methods: {
        initSearchData () {
          this.searchData = {
              dateTime: [],
              dateTime2: [],
              compare: false,
              selectedDimension: []
          }
          this.initTag()
          this.collectedTagsId = [[-1], [-1, 0], [-1, 0, 11], [-1, 0, 11, 101]]
          this.dimensionData = [
            {
              value: -1,
              label: '留言',
              children: [
                {
                  value: 0,
                  label: 'ly_PC端',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  },
                  children: [
                    {
                      value: 11,
                      label: '坐席',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 101,
                          label: '城市站坐席',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 102,
                          label: '学校坐席',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 103,
                          label: 'PC智能客服',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    },
                    {
                      value: 12,
                      label: '留言',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 123,
                          label: '城市站留言',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    },
                    {
                      value: 21,
                      label: '报名',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 131,
                          label: '合作学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 135,
                          label: '非合作学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 134,
                          label: '无学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    }
                  ]
                },
                {
                  value: 1,
                  label: 'M端',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  },
                  children: [
                    {
                      value: 11,
                      label: 'm坐席',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 101,
                          label: 'm城市站坐席',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 102,
                          label: 'm学校坐席',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 103,
                          label: 'm智能客服',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    },
                    {
                      value: 12,
                      label: 'm留言',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 123,
                          label: 'm城市站留言',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    },
                    {
                      value: 21,
                      label: 'm报名',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 131,
                          label: 'm合作学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 135,
                          label: 'm非合作学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 134,
                          label: 'm无学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    }
                  ]
                },
                {
                  value: 2,
                  label: '400电话',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  },
                  children: [
                    {
                      value: 51,
                      label: '400公用电话',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      }
                    },
                    {
                      value: 51,
                      label: '400KA电话',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      }
                    },
                    {
                      value: 51,
                      label: '400站长电话',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      }
                    },
                    {
                      value: 51,
                      label: '400学校电话',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      }
                    }
                  ]
                },
                {
                  value: 3,
                  label: 'APP',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  }
                },
                {
                  value: 4,
                  label: '小程序',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  }
                },
                {
                  value: 5,
                  label: '其它',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  }
                }
              ]
            },
            {
              value: -2,
              label: '终端',
              children: [
                {
                  value: 0,
                  label: 'zd_PC端',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  },
                  children: [
                    {
                      value: 11,
                      label: '坐席',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 101,
                          label: '城市站坐席',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 102,
                          label: '学校坐席',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 103,
                          label: 'PC智能客服',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    },
                    {
                      value: 12,
                      label: '留言',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 123,
                          label: '城市站留言',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    },
                    {
                      value: 21,
                      label: '报名',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 131,
                          label: '合作学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 135,
                          label: '非合作学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 134,
                          label: '无学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    }
                  ]
                },
                {
                  value: 1,
                  label: 'M端',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  },
                  children: [
                    {
                      value: 11,
                      label: 'm坐席',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 101,
                          label: 'm城市站坐席',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 102,
                          label: 'm学校坐席',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 103,
                          label: 'm智能客服',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    },
                    {
                      value: 12,
                      label: 'm留言',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 123,
                          label: 'm城市站留言',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    },
                    {
                      value: 21,
                      label: 'm报名',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      },
                      children: [
                        {
                          value: 131,
                          label: 'm合作学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 135,
                          label: 'm非合作学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        },
                        {
                          value: 134,
                          label: 'm无学校报名',
                          number: 12024,
                          percent: '15.35%',
                          compare: {
                            number: 12025,
                            percent: '16.72%'
                          }
                        }
                      ]
                    }
                  ]
                },
                {
                  value: 2,
                  label: '400电话',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  },
                  children: [
                    {
                      value: 51,
                      label: '400公用电话',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      }
                    },
                    {
                      value: 51,
                      label: '400KA电话',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      }
                    },
                    {
                      value: 51,
                      label: '400站长电话',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      }
                    },
                    {
                      value: 51,
                      label: '400学校电话',
                      number: 12024,
                      percent: '15.35%',
                      compare: {
                        number: 12025,
                        percent: '16.72%'
                      }
                    }
                  ]
                },
                {
                  value: 3,
                  label: 'APP',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  }
                },
                {
                  value: 4,
                  label: '小程序',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  }
                },
                {
                  value: 5,
                  label: '其它',
                  number: 12024,
                  percent: '15.35%',
                  compare: {
                    number: 12025,
                    percent: '16.72%'
                  }
                }
              ]
            }
          ]
          this.markStarBool = false
          this.treeState = {
            show: [false, true, true, true],
            activeIndex: []
          }
          this.dimensionTitle = ''
        },
        createChart () {
          this.xData = ['2018-12-10', '2018-12-11', '2018-12-12', '2018-12-13', '2018-12-14', '2018-12-15', '2018-12-16']
          this.yData = [820, 932, 901, 934, 1290, 1330, 1320]
          let originalSource_lineChart_option = {
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: this.xData
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                data: this.yData,
                type: 'line',
                smooth: true
            }]
          }
          this.$echarts.init(document.getElementById('originalSource_lineChart')).setOption(originalSource_lineChart_option)
        },
        initTabsTableData () {
          this.getNoCompare0()
        },
        getNoCompare0 () {
          this.tabsTableData = {
            tabsLabel: ['按ly_PC端', '按坐席', '按留言', '按报名'],
            tabTable: {
              colName: ['时段', '原始单总量', '坐席', '留言', '报名'],
              colData: [
                {
                  col1: '总计',
                  col2: '132428',
                  col3: '132428(27.01%)',
                  col4: '132428(27.01%)',
                  col5: '132428(27.01%)',
                  expandInfo: {
                    colName: ['城市站坐席', '学校坐席', 'PC智能客服'],
                    colData: [
                      {
                        col1: '1-1child-132428(27.01%)',
                        col2: '1-2child-132428(27.01%)',
                        col3: '1-3child-132428(27.01%)'
                      },
                      {
                        col1: '2-1child-132428(27.01%)',
                        col2: '2-2child-132428(27.01%)',
                        col3: '2-3child-132428(27.01%)'
                      }
                    ]
                  }
                },
                {
                  col1: '12-10',
                  col2: '132428',
                  col3: '132428(27.01%)',
                  col4: '132428(27.01%)',
                  col5: '132428(27.01%)'
                },
                {
                  col1: '12-11',
                  col2: '132428',
                  col3: '132428(27.01%)',
                  col4: '132428(27.01%)',
                  col5: '132428(27.01%)'
                },
                {
                  col1: '12-12',
                  col2: '132428',
                  col3: '132428(27.01%)',
                  col4: '132428(27.01%)',
                  col5: '132428(27.01%)',
                  expandInfo: {
                    colName: ['城市站坐席', '学校坐席', 'PC智能客服'],
                    colData: [
                      {
                        col1: '10-1child-132428(27.01%)',
                        col2: '10-2child-132428(27.01%)',
                        col3: '10-3child-132428(27.01%)'
                      },
                      {
                        col1: '11-1child-132428(27.01%)',
                        col2: '11-2child-132428(27.01%)',
                        col3: '11-3child-132428(27.01%)'
                      }
                    ]
                  }
                }
              ]
            }
          }
        },
        getNoCompare1 () {
          this.tabsTableData = {
            tabsLabel: ['按ly_PC端', '按坐席', '按留言', '按报名'],
            tabTable: {
              colName: ['时段', '坐席总量', '城市站坐席', '学校坐席', 'PC智能客服'],
              colData: [
                {
                  col1: '总计',
                  col2: '132428',
                  col3: '132428(27.01%)',
                  col4: '132428(27.01%)',
                  col5: '132428(27.01%)'
                },
                {
                  col1: '12-10',
                  col2: '132428',
                  col3: '132428(27.01%)',
                  col4: '132428(27.01%)',
                  col5: '132428(27.01%)'
                },
                {
                  col1: '12-11',
                  col2: '132428',
                  col3: '132428(27.01%)',
                  col4: '132428(27.01%)',
                  col5: '132428(27.01%)'
                },
                {
                  col1: '12-12',
                  col2: '132428',
                  col3: '132428(27.01%)',
                  col4: '132428(27.01%)',
                  col5: '132428(27.01%)'
                }
              ]
            }
          }
        },
        getCompare () {
          this.tabsTableData = {
            tabsLabel: ['按ly_PC端', '按坐席', '按留言', '按报名'],
            tabTable: {
              colName: ['时段', '原始单总量', '坐席', '留言', '报名'],
              colData: [
                {
                  col1: '总计',
                  col2: {
                    one: '132421',
                    two: '132421',
                    change: '+24732(+8.06%)',
                    type: 'up'
                  },
                  col3: {
                    one: '132422',
                    two: '132422',
                    change: '+24732(+8.06%)',
                    type: 'equal'
                  },
                  col4: {
                    one: '132423',
                    two: '132423',
                    change: '+24732(+8.06%)',
                    type: 'down'
                  },
                  col5: {
                    one: '132428',
                    two: '132428',
                    change: '+24732(+8.06%)',
                    type: 'down'
                  }
                },
                {
                  col1: '12-07 VS 12-08',
                  col2: {
                    one: '132428',
                    two: '132428',
                    change: '+24732(+8.06%)',
                    type: 'up'
                  },
                  col3: {
                    one: '132428',
                    two: '132428',
                    change: '+24732(+8.06%)',
                    type: 'down'
                  },
                  col4: {
                    one: '132428',
                    two: '132428',
                    change: '+24732(+8.06%)',
                    type: 'equal'
                  },
                  col5: {
                    one: '132428',
                    two: '132428',
                    change: '+24732(+8.06%)',
                    type: 'down'
                  }
                }
              ]
            }
          }
        },
        chooseCompare () {
          if (this.searchData.compare) {
            this.getCompare()
          } else {
            if (this.activeTab === '0') {
              this.getNoCompare0()
            } else {
              this.getNoCompare1()
            }
          }
        },
        changeTab () {
          if (this.searchData.compare) {
            this.getCompare()
          } else {
            if (this.activeTab === '0') {
              this.getNoCompare0()
            } else {
              this.getNoCompare1()
            }
          }
        },
        onClear () {
          this.initSearchData()
          this.bool = !this.bool
        },
        onSubmit () {
          this.treeState.activeIndex = this.switchIdtoIndex(this.searchData.selectedDimension)
          if (this.treeState.activeIndex.length === 1) {
            this.treeState.show = [false, true, false, false]
          } else if (this.treeState.activeIndex.length === 2) {
            this.treeState.show = [false, true, false, false]
          } else if (this.treeState.activeIndex.length === 3) {
            this.treeState.show = [false, true, true, false]
          } else {
            this.treeState.show = [false, true, true, true]
          }
          this.dimensionTitle = this.selectedDimensionLabel
        },
        expandTreeNode (e, type, index) {
          if (type === 'one') {
            if (this.treeState.activeIndex[1] === undefined) {
              this.$set(this.treeState.activeIndex, 1, index)
              if (this.treeState.activeIndex[1] === 0) {
                this.$set(this.treeState.show, 2, false)
                this.$set(this.treeState.show, 3, false)
              }
            } else {
              if (this.treeState.activeIndex[1] !== index) {
                this.$set(this.treeState.activeIndex, 1, index)
                this.$set(this.treeState.activeIndex, 2, 0)
                this.$set(this.treeState.activeIndex, 3, 0)
                this.$set(this.treeState.show, 2, true)
                this.$set(this.treeState.show, 3, true)
              } else {
                this.$set(this.treeState.show, 2, !this.treeState.show[2])
                this.$set(this.treeState.show, 3, false)
              }
            }
          }

          if (type === 'two') {
            if (this.treeState.activeIndex[2] === undefined) {
              this.$set(this.treeState.activeIndex, 2, index)
              this.$set(this.treeState.show, 3, !this.treeState.show[3])
            } else {
              if (this.treeState.activeIndex[2] !== index) {
                this.$set(this.treeState.activeIndex, 2, index)
                this.$set(this.treeState.activeIndex, 3, 0)
                this.$set(this.treeState.show, 3, true)
              } else {
                this.$set(this.treeState.show, 3, !this.treeState.show[3])
              }
            }
          }

          if (type === 'three') {
            this.$set(this.treeState.activeIndex, 3, index)
          }
        },
        selectCollectedTag (tag) {
          this.searchData.selectedDimension = this.switchLabelToId(tag)
          this.markStarBool = true
        },
        cancelCollect (tag) {
          let index = this.collectedTagsLabel.indexOf(tag)
          if (index > -1) {
            this.collectedTagsLabel.splice(index, 1)
            if (tag === this.selectedDimensionLabel) {
              this.cancelMarkStar()
              this.onDelTag()
            }
          }
        },
        initTag () {
            let that = this
            this.$ajax.get('/original_source/init_lable')
                .then(function (res) {
                    if (res.data !== undefined) {
//                        that.dimensionData = res.data
                    }
                })
                .catch(function(err) {

                })
        },
        onDelTag () {
            let that = this
            this.$ajax.post('/original_source/del_lable', {lable_name:that.selectedDimensionLabel})
                .then(function (res) {
                    if (res.data !== undefined) {

                    }
                })
                .catch(function(err) {

                })
        },
        onSaveTag () {
          if (!this.markStarBool) {
            if (this.collectedTagsLabel.indexOf(this.selectedDimensionLabel.trim()) === -1) {
              if (this.selectedDimensionLabel.trim() !== '') {
                let that = this
                this.$ajax.post('/original_source/add_lable', {lable_name:that.selectedDimensionLabel})
                  .then(function (res) {
                      if (res.data !== undefined) {
                          that.collectedTagsLabel.push(that.selectedDimensionLabel)
                          that.markStar()
                      }
                  })
                  .catch(function(err) {

                  })
              } else {
                this.$message('请先选择维度')
              }
            } else {
              this.$message('该维度已被保存')
            }
          } else {
              this.cancelCollect(this.selectedDimensionLabel)
              this.cancelMarkStar()
//              let that = this
//              this.$ajax.post('/original_source/add_lable', that.searchData)
//                  .then(function (res) {
//                      if (res.data !== undefined) {
//                          this.cancelCollect(this.selectedDimensionLabel)
//                          this.cancelMarkStar()
//                      }
//                  })
//                  .catch(function(err) {
//
//                  })
          }
        },
        handleChangeDimension () {
          if (this.collectedTagsLabel.indexOf(this.selectedDimensionLabel) > -1) {
            this.markStar()
          } else {
            this.cancelMarkStar()
          }
        },
        switchIdtoIndex (ids) {
          let indexArr = []
          for (let i = 0; i < this.dimensionData.length; i++) {
            if (ids[0] === this.dimensionData[i].value) {
              indexArr.push(i)
              if (this.dimensionData[i].children !== undefined && this.dimensionData[i].children.length > 0) {
                for (let j = 0; j < this.dimensionData[i].children.length; j++)
                if (ids[1] === this.dimensionData[i].children[j].value) {
                  indexArr.push(j)
                  if (this.dimensionData[i].children[j].children !== undefined && this.dimensionData[i].children[j].children.length > 0) {
                    for (let k = 0; k < this.dimensionData[i].children[j].children.length; k++)
                    if (ids[2] === this.dimensionData[i].children[j].children[k].value) {
                      indexArr.push(k)
                      if (this.dimensionData[i].children[j].children[k].children !== undefined && this.dimensionData[i].children[j].children[k].children.length > 0) {
                        for (let m = 0; m < this.dimensionData[i].children[j].children[k].children.length; m++)
                        if (ids[3] === this.dimensionData[i].children[j].children[k].children[m].value) {
                          indexArr.push(m)
                        }
                      }
                    }
                  }
                }
              }
            }
          }
          return indexArr
        },
        switchIdtoLabel (ids) {
          let one = '', two = '', three = '', four = ''
          for (let i = 0; i < this.dimensionData.length; i++) {
            if (this.dimensionData[i].value === ids[0]) {
              one = this.dimensionData[i].label
              if (this.dimensionData[i].children !== undefined && this.dimensionData[i].children.length > 0) {
                for (let j = 0; j < this.dimensionData[i].children.length; j++) {
                  if (this.dimensionData[i].children[j].value === ids[1]) {
                    two = '-' + this.dimensionData[i].children[j].label
                    if (this.dimensionData[i].children[j].children !== undefined && this.dimensionData[i].children[j].children.length > 0) {
                      for (let k = 0; k < this.dimensionData[i].children[j].children.length; k++) {
                        if (this.dimensionData[i].children[j].children[k].value === ids[2]) {
                          three = '-' + this.dimensionData[i].children[j].children[k].label
                          if (this.dimensionData[i].children[j].children[k].children !== undefined &&
                             this.dimensionData[i].children[j].children[k].children.length > 0) {
                             for (let m = 0; m < this.dimensionData[i].children[j].children[k].children.length; m++) {
                               if (this.dimensionData[i].children[j].children[k].children[m].value === ids[3]) {
                                 four = '-' + this.dimensionData[i].children[j].children[k].children[m].label
                               }
                             }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
          return one + two + three + four
        },
        switchLabelToId (labels) {
          let selectedDimensionArr = [], tempArr = labels.split('-')
          for (let i = 0; i < this.dimensionData.length; i++) {
            if (this.dimensionData[i].label === tempArr[0]) {
              selectedDimensionArr.push(this.dimensionData[i].value)
              if (this.dimensionData[i].children !== undefined && this.dimensionData[i].children.length > 0) {
                for (let j = 0; j < this.dimensionData[i].children.length; j++) {
                  if (this.dimensionData[i].children[j].label === tempArr[1]) {
                    selectedDimensionArr.push(this.dimensionData[i].children[j].value)
                    if (this.dimensionData[i].children[j].children !== undefined && this.dimensionData[i].children[j].children.length > 0) {
                      for (let k = 0; k < this.dimensionData[i].children[j].children.length; k++) {
                        if (this.dimensionData[i].children[j].children[k].label === tempArr[2]) {
                          selectedDimensionArr.push(this.dimensionData[i].children[j].children[k].value)
                          if (this.dimensionData[i].children[j].children[k].children !== undefined &&
                             this.dimensionData[i].children[j].children[k].children.length > 0) {
                             for (let m = 0; m < this.dimensionData[i].children[j].children[k].children.length; m++) {
                               if (this.dimensionData[i].children[j].children[k].children[m].label === tempArr[3]) {
                                 selectedDimensionArr.push(this.dimensionData[i].children[j].children[k].children[m].value)
                               }
                             }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
            return selectedDimensionArr
          }
        },
        drawStar () {
          let r = 4
          let R = 8
          let x = 9
          let y = 9
          let rot = 0

          let canvas = document.getElementById('starCanvas')
          let cxt = canvas.getContext('2d')
          cxt.beginPath()
          for ( var i = 0 ; i < 5 ; i ++) {
            cxt.lineTo(Math.cos((18+72*i - rot)/180*Math.PI) * R + x ,- Math.sin((18+72*i - rot )/180*Math.PI) * R + y)
            cxt.lineTo(Math.cos((54+72*i - rot)/180*Math.PI) * r + x ,- Math.sin((54+72*i - rot )/180*Math.PI) * r + y)
          }
          cxt.closePath()
          cxt.lineWidth = '2'
          cxt.strokeStyle = '#3399FF'
          cxt.fillStyle = '#f4f7f9'
          cxt.stroke()
          cxt.fill()

          let canvas2 = document.getElementById('starCanvasMark')
          let cxt2 = canvas2.getContext('2d')
          cxt2.beginPath()
          for ( var i = 0 ; i < 5 ; i ++) {
            cxt2.lineTo(Math.cos((18+72*i - rot)/180*Math.PI) * R + x ,- Math.sin((18+72*i - rot )/180*Math.PI) * R + y)
            cxt2.lineTo(Math.cos((54+72*i - rot)/180*Math.PI) * r + x ,- Math.sin((54+72*i - rot )/180*Math.PI) * r + y)
          }
          cxt2.closePath()
          cxt2.lineWidth = '2'
          cxt2.strokeStyle = '#3399FF'
          cxt2.fillStyle = '#3399FF'
          cxt2.stroke()
          cxt2.fill()
        },
        markStar () {
          this.markStarBool = true
        },
        cancelMarkStar () {
          this.markStarBool = false
        },
        handleReceiveDateChange (obj) {
          this.searchData.dateTime = obj.dateTime
          this.searchData.dateTime2 = obj.dateTime2
          this.searchData.compare = obj.compare
          this.chooseCompare()
        },
        tableRowClassName({row, rowIndex}) {
            if (rowIndex === 0) {
                return 'sumClass'
            }
            return ''
        },
        setCellStyle (val) {
            let cells = []
            let index = 3
            for (let col in val.row) {
              if (col !== 'col1') {
                cells.push({
                  index: index,
                  name: col
                })
                index = index + 3
              }
            }
            return this.setFontColor(val, cells)
        },
        setFontColor (val, cells) {
          for (let i = 0; i < cells.length; i++) {
            if (val.columnIndex === cells[i].index) {
              if (val.row[cells[i].name].type === 'up') {
                return { color : 'red!important', fontWeight: 'bold' }
              } else if (val.row[cells[i].name].type === 'down') {
                return { color : 'green!important', fontWeight: 'bold' }
              } else {
                return { color : '#777!important', fontWeight: 'bold' }
              }
            }
          }
        }
     }
  }

</script>

<style scoped>
  @import "./../../assets/css/page/OriginalSource.css";
</style>
