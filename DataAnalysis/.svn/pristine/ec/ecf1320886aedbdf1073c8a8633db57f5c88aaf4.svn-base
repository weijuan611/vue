<template>
    <page-wrap>

        <!-- 标题栏 -->
        <page-title title="关键词-指数观察" />

        <!-- 搜索栏 -->
        <search-wrap>
          <el-form ref="search-form" label-width="70px" :model="searchData" size="mini">
              <el-row :gutter="20">
                  <el-col :span="24">
                      <el-form-item label="关键词：">
                        <el-input v-model="searchData.keyword" style="margin-right: 10px;" @keyup.enter.native="onSubmit"></el-input>

                        <el-tag
                          type="success"
                          size="medium"
                          style="margin-right: 10px;"
                          :key="tag"
                          v-for="tag in searchData.comapredKeyword"
                          closable
                          :disable-transitions="false"
                          @close="closeCompareKeyword(tag)">
                          {{tag}}
                        </el-tag>

                        <el-input
                          v-if="addKeywordInputVisible"
                          v-model="addKeywordInputValue"
                          ref="saveTagInput"
                          @keyup.enter.native="addKeywordInputConfirm"
                          @blur="addKeywordInputConfirm"
                        >
                        </el-input>
                        <el-button v-else type="warning" plain icon="el-icon-circle-plus" @click="showAddKeywordInput">添加对比词</el-button>
                        <el-button type="primary" @click="onSubmit">查询</el-button>

                      </el-form-item>
                  </el-col>
              </el-row>
            </el-form>
        </search-wrap>

        <div v-if="keywordData.value !== '' && !noResult" class="detailWrap"
          v-loading="loading"
          element-loading-text="数据刷新中，请稍后"
          element-loading-spinner="el-icon-loading"
          element-loading-background="rgba(251, 251, 251, 0.8)">

          <div v-if="showCompare" class="moreKeywords">
            <table cellspacing="0">
              <thead>
                <tr>
                  <th width="200px">关键词</th>
                  <th>PC综合评分</th>
                  <th>PC百度排名</th>
                  <th>PC搜索量</th>
                  <th>PC推广指数</th>
                  <th>PC收录指数</th>
                  <th>M综合评分</th>
                  <th>M百度排名</th>
                  <th>M搜索量</th>
                  <th>M推广指数</th>
                  <th>M收录指数</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="item in keywordsList">
                  <tr :class="[item.value === selectedKeyword ? 'hover' : '']" @click="selectKeyword(item.value)">
                    <td>{{item.value}}</td>
                    <td>{{item.pc.overallScore}}</td>
                    <td>{{item.pc.baiduRank}}</td>
                    <td>{{item.pc.searchVolume}}</td>
                    <td>{{item.pc.promotionIndex}}</td>
                    <td>{{item.pc.includeIndex}}</td>
                    <td>{{item.m.overallScore}}</td>
                    <td>{{item.m.baiduRank}}</td>
                    <td>{{item.m.searchVolume}}</td>
                    <td>{{item.m.promotionIndex}}</td>
                    <td>{{item.m.includeIndex}}</td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          <span class="keyword">{{keywordData.value}}</span>
          <el-button type="primary" plain icon="el-icon-refresh" size="mini" @click="onRefresh(keywordData.value)" :disabled="keywordData.update_time === '0000-00-00 00:00:00'">刷新</el-button>

          <div class="clearfix">
          <div class="pc">
            <div class="ball-detail">
                <i :class="['ball', rankBallClass_pc]"></i>
              <div class="detail">
                  <p><b>PC百度排名</b><span>{{keywordData.pcData.baiduRank}}</span></p>
                  <p><b>PC搜索量</b><span>{{keywordData.pcData.searchVolume}}</span></p>
                  <p><b>排名页面</b><span>{{keywordData.pcData.rankPage}}</span></p>
              </div>
            </div>
            <div class="tips">
              <p><i class="el-alert__icon el-icon-info"></i>PC优化建议</p>
              <ul>
                  <template v-for="(item, index) in keywordData.pcData.tips">
                  <li>{{index + 1}}. {{item}}</li>
                </template>
              </ul>
            </div>
            <div class="index mr">
              <span class="title">PC推广指数</span>
                <mark-block :number="keywordData.pcData.promotion.score">{{keywordData.pcData.promotion.score}}分</mark-block>
              <ul>
                  <template v-for="item in keywordData.pcData.promotion.data">
                  <li @click="changeTab_PC({label: item.name})"><b>{{item.name}}</b><span :class="item.state === '未达标' ? 'red' : ''">{{item.value}} ({{item.state}})</span></li>
                </template>
              </ul>
            </div>
            <div class="index">
              <span class="title">PC收录指数</span>
                <mark-block :number="keywordData.pcData.include.score">{{keywordData.pcData.include.score}}分</mark-block>
              <ul>
                  <template v-for="item in keywordData.pcData.include.data">
                  <li @click="changeTab_PC({label: item.name})"><b>{{item.name}}</b><span :class="item.state === '未达标' ? 'red' : ''">{{item.value}} ({{item.state}})</span></li>
                </template>
              </ul>
            </div>
              <div class="tab-table clearfix">
                <div class="buttons">
                  <el-button type="text" @click="submitInclude('pc', 'all')">全部提交</el-button>
                  <el-button type="text" style="display: none;" @click="onExport('pc')">导出URL</el-button>
                </div>
                <el-tabs type="border-card" @tab-click="changeTab_PC" v-model="pcTabName">
                  <el-tab-pane key="学校" label="学校" name="学校"></el-tab-pane>
                  <el-tab-pane key="课程" label="课程" name="课程"></el-tab-pane>
                  <el-tab-pane key="新闻" label="新闻" name="新闻"></el-tab-pane>
                  <el-tab-pane key="头条" label="头条" name="头条"></el-tab-pane>
                  <el-tab-pane key="知道" label="知道" name="知道"></el-tab-pane>
                  <el-table
                   v-loading="loadingPCTable"
                   :data="keywordData.pcData.tableData"
                   :row-style="setRowStyle"
                   style="width: 100%">
                   <el-table-column
                     prop="url"
                     label="URL"
                     :show-overflow-tooltip="isTrue">
                   </el-table-column>
                   <el-table-column
                     prop="title"
                     label="标题"
                     :show-overflow-tooltip="isTrue">
                   </el-table-column>
                   <el-table-column
                     prop="number"
                     width="50px"
                     label="数量">
                   </el-table-column>
                   <el-table-column
                     prop="density"
                     width="50px"
                     label="密度">
                   </el-table-column>
                   <el-table-column
                     prop="isInclude"
                     width="75px"
                     label="是否收录">
                   </el-table-column>
                   <el-table-column label="操作" width="100px" header-align="center" >
                     <template slot-scope="props">
                       <el-button type="text" :disabled="props.row.isInclude === '已收录' || props.row.isInclude === '已提交'" @click="submitInclude('pc', props.row.id,props.row.url,pcTabName)">提交收录</el-button>
                     </template>
                   </el-table-column>
                 </el-table>
                </el-tabs>
              </div>
            </div>

          <div class="m">
            <div class="ball-detail">
                <i :class="['ball', rankBallClass_m]"></i>
              <div class="detail">
                  <p><b>M百度排名</b>{{keywordData.mData.baiduRank}}</p>
                  <p><b>M搜索量</b>{{keywordData.mData.searchVolume}}</p>
                  <p><b>排名页面</b>{{keywordData.mData.rankPage}}</p>
              </div>
            </div>
            <div class="tips">
              <p><i class="el-alert__icon el-icon-info"></i>M优化建议</p>
              <ul>
                  <template v-for="(item, index) in keywordData.mData.tips">
                  <li>{{index + 1}}. {{item}}</li>
                </template>
              </ul>
            </div>
            <div class="index mr">
              <span class="title">M推广指数</span>
                <mark-block :number="keywordData.mData.promotion.score">{{keywordData.mData.promotion.score}}分</mark-block>
              <ul>
                  <template v-for="item in keywordData.mData.promotion.data">
                  <li @click="changeTab_M({label: item.name})"><b>{{item.name}}</b><span :class="item.state === '未达标' ? 'red' : ''">{{item.value}} ({{item.state}})</span></li>
                </template>
              </ul>
            </div>
            <div class="index">
              <span class="title">M收录指数</span>
                <mark-block :number="keywordData.mData.include.score">{{keywordData.mData.include.score}}分</mark-block>
                <ul>
                  <template v-for="item in keywordData.mData.include.data">
                  <li @click="changeTab_M({label: item.name})"><b>{{item.name}}</b><span :class="item.state === '未达标' ? 'red' : ''">{{item.value}} ({{item.state}})</span></li>
                  </template>
                </ul>
            </div>
            <div class="tab-table clearfix">
                <div class="buttons">
                  <el-button type="text" @click="submitInclude('m', 'all')">全部提交</el-button>
                  <el-button type="text" style="display: none;" @click="onExport('m')">导出URL</el-button>
                </div>
                <el-tabs type="border-card" @tab-click="changeTab_M" v-model="mTabName">
                  <el-tab-pane key="学校" label="学校" name="学校"></el-tab-pane>
                  <el-tab-pane key="课程" label="课程" name="课程"></el-tab-pane>
                  <el-tab-pane key="新闻" label="新闻" name="新闻"></el-tab-pane>
                  <el-tab-pane key="头条" label="头条" name="头条"></el-tab-pane>
                  <el-tab-pane key="知道" label="知道" name="知道"></el-tab-pane>
                  <el-table
                   id="table"
                   v-loading="loadingMTable"
                   :data="keywordData.mData.tableData"
                   :row-style="setRowStyle"
                   style="width: 100%">
                   <el-table-column
                     prop="url"
                     label="URL"
                     :show-overflow-tooltip="isTrue">
                   </el-table-column>
                   <el-table-column
                     prop="title"
                     label="标题"
                     :show-overflow-tooltip="isTrue">
                   </el-table-column>
                   <el-table-column
                     prop="number"
                     width="50px"
                     label="数量">
                   </el-table-column>
                   <el-table-column
                     prop="density"
                     width="50px"
                     label="密度">
                   </el-table-column>
                   <el-table-column
                     prop="isInclude"
                     width="75px"
                     label="是否收录">
                   </el-table-column>
                   <el-table-column label="操作" width="100px" header-align="center">
                     <template slot-scope="props">
                       <el-button type="text" :disabled="props.row.isInclude === '已收录' || props.row.isInclude === '已提交'" @click="submitInclude('m', props.row.id,props.row.url,mTabName)">提交收录</el-button>
                     </template>
                   </el-table-column>
                 </el-table>
                </el-tabs>
                </div>
            </div>
          </div>

        </div>

    </page-wrap>
</template>


<script>
    import PageWrap from './../../components/pageStructure/PageWrap.vue'
    import SearchWrap from './../../components/pageStructure/SearchWrap.vue'
    import TableWrap from './../../components/pageStructure/TableWrap.vue'
    import paginationWrap from './../../components/pageStructure/paginationWrap.vue'
    import PageTitle from './../../components/pageStructure/PageTitle.vue'
    import MarkBlock from './../../components/base/MarkBlock.vue'
    import { mapState } from 'vuex'

    export default {
        components: {
            PageWrap, SearchWrap, TableWrap, paginationWrap, PageTitle, MarkBlock
        },
        data () {
            return {
              searchData: {
                keyword: '',
                comapredKeyword: []
              },
              keywordData: {
                value: '',
                pcData: {},
                mData: {}
              },
              keywordsList: [],
              addKeywordInputVisible: false,
              addKeywordInputValue: '',
              showCompare: false,
              isTrue: true,
              loadingPCTable: false,
              loadingMTable: false,
              rankBallClass_pc: '',
              rankBallClass_m: '',
              selectedKeyword: '',
              loading: false,
              noResult: false,
              pcTabName: '学校',
              mTabName: '学校'
            }
        },
        computed: {
            ...mapState('navTabs',[
                'keyword_to_observe'
            ])
        },
        mounted () {
          // let contentHeight = document.getElementsByClassName('pageWrap')[0].offsetHeight
          // let table_offsetTop = document.getElementById('table')
          // console.log('contentHeight', contentHeight)
          // console.log('table_offsetTop', table_offsetTop)

          this.initSearchData()
          if (this.keyword_to_observe.keyword !== '') {
            this.onSubmit()
          }
        },
        watch: {
          keyword_to_observe () {
            this.initSearchData()
            if (this.keyword_to_observe.keyword !== '') {
              this.onSubmit()
            }
          }
        },
        methods: {
          initSearchData () {
            this.searchData = {
              keyword: this.keyword_to_observe.keyword,
              comapredKeyword: []
            }
          },
          changeTab_PC (tab) {
            this.pcTabName = tab.label
            this.loadingPCTable = true
            setTimeout(() => {
              this.loadingPCTable = false
            }, 500)
            let keyword = this.selectedKeyword
            let title = tab.label

            this.onsubmitChangeTab(keyword, title, 'pc')
            // this.keywordData.pcData.tableData = []
          },
          changeTab_M (tab) {
            this.mTabName = tab.label
            this.loadingMTable = true
            setTimeout(() => {
              this.loadingMTable = false
            }, 500)

            let keyword = this.selectedKeyword
            let title = tab.label
            this.onsubmitChangeTab(keyword, title, 'm')
          },
          onsubmitChangeTab(keyword, title, type) {
              let that = this
              this.$ajax.post('/observe/changeTab',{keyword:keyword,title:title,type:type})
                  .then(function(res) {
                      if (res.data !== undefined) {
                          if (type === 'm') {
                              that.keywordData.mData.tableData = res.data.M
                          } else {
                              that.keywordData.pcData.tableData = res.data.PC
                          }
                      }
                  })
                  .catch(function(err) {
                  })
          },
          onSubmit () {
            if (this.searchData.keyword !== '') {
            let that = this
            that.loading = true
            this.$ajax.post('/observe/index',{searchData:that.searchData})
                .then(function(res) {
                  if (res.data.type === true) {
                    that.noResult = false
                    if (res.data !== undefined) {
                      if (that.searchData.comapredKeyword.length > 0) {
                          that.showCompare = true
                          let comapredKeyword = that.searchData.comapredKeyword
                          that.keywordsList = res.data.info
                      } else {
                          that.showCompare = false
                          that.keywordData = res.data.info
                          let keyword = that.searchData.keyword
                          that.rankBallClass_pc = that.getRankBallClass(that.keywordData.pcData.overallScore)
                          that.rankBallClass_m = that.getRankBallClass(that.keywordData.mData.overallScore)
                          that.selectedKeyword = that.keywordData.value
                      }
                    }
                  } else {
                      that.noResult = true
                      that.$message({
                          type: 'info',
                          message: '数据库中无此关键词!'
                      })
                  }
                  that.loading = false
                })
                .catch(function(err) {
                })
            } else {
              this.$message({
                type: 'warning',
                message: '请输入关键词!'
              })
            }
          },
          selectKeyword (val) {
            this.selectedKeyword = val
            this.loading = true
            let that = this
            this.$ajax.post('/observe/index',{keyword:val,type:true,searchData:that.searchData})
                .then(function(res) {
                    if (res.data.type === true) {
                        if (res.data !== undefined) {
                            that.showCompare = true
                            let comapredKeyword = that.searchData.comapredKeyword
                            that.keywordData = res.data.info
                            that.rankBallClass_pc = that.getRankBallClass(that.keywordData.pcData.overallScore)
                            that.rankBallClass_m = that.getRankBallClass(that.keywordData.mData.overallScore)
                            that.loading = false
                        }
                    } else {
                        this.$message({
                            type: 'info',
                            message: '数据库中无此关键词!'
                        })
                    }
                })
                .catch(function(err) {
                })
          },
          onRefresh (kw) {
          let that = this
          this.$ajax.get('/lexicon/refresh?kw='+kw).then(function (res) {
              that.$message({
                  type: 'success',
                  message: "已加入更新队列，请等待更新"
              })
              that.onSubmit()
          })
          },
          onExport () {
          },
          getRankBallClass (type) {
            switch (type) {
              case '完美推广':
                return 'ball1'
              case '一步之遥':
                return 'ball2'
              case '初见成效':
                return 'ball3'
              case '无效推广':
                return 'ball4'
              case '毫无推广':
                return 'ball5'
            }
          },
          submitInclude (media, id, url,tabname) {
            this.$confirm('确定提交收录吗?', '提示', {
               confirmButtonText: '确定',
               cancelButtonText: '取消',
               type: 'warning'
             }).then(() => {
                let that = this
                this.$ajax.post('/observe/included',{id:id,url:url})
                    .then(function(res) {
                        if (res.data.type === true) {
                            if (res.data !== undefined) {
                                this.$message({
                                    type: 'success',
                                    message: '收录成功!'
                                })
                            }
                        } else {
                            this.$message({
                                type: 'info',
                                message: '数据库中无此关键词!'
                            })
                        }
                    })
                    .catch(function(err) {
                    })

                if (media === 'pc') {
                    let keyword = this.selectedKeyword
                    this.onsubmitChangeTab(keyword, tabname, 'pc')
                    this.loadingPCTable = true
                    setTimeout(() => {this.loadingPCTable = false}, 500)
                    // this.keywordData.pcData.tableData = []
                } else {
                    let keyword = this.selectedKeyword
                    this.onsubmitChangeTab(keyword, tabname, 'm')
                    this.loadingMTable = true
                    setTimeout(() => {this.loadingMTable = false}, 500)
                    // this.keywordData.mData.tableData = []
                }
             }).catch(() => {
               this.$message({
                 type: 'info',
                 message: '已取消收录'
               })
             })
          },
          setRowStyle ({row}) {
            if (row.isInclude === '否') {
              return { color: 'red' }
            }
          },
          showAddKeywordInput () {
            this.addKeywordInputVisible = true
            this.$nextTick(_ => {
              this.$refs.saveTagInput.$refs.input.focus()
            })
          },
          closeCompareKeyword (tag) {
            this.searchData.comapredKeyword.splice(this.searchData.comapredKeyword.indexOf(tag), 1)
          },
          addKeywordInputConfirm () {
            let addKeywordInputValue = this.addKeywordInputValue
            if (addKeywordInputValue) {
              if (addKeywordInputValue.trim() === '') {
                this.addKeywordInputVisible = false
                this.addKeywordInputValue = ''
              } else if (this.searchData.comapredKeyword.length > 3) {
                this.$message({
                  type: 'warning',
                  message: '最多同时查看5个关键词!'
                })
              } else if (addKeywordInputValue.trim() === this.searchData.keyword.trim() || this.searchData.comapredKeyword.indexOf(addKeywordInputValue.trim()) > -1) {
                this.$message({
                  type: 'warning',
                  message: '关键词已存在，请输入其它关键词!'
                })
              } else {
                this.searchData.comapredKeyword.push(addKeywordInputValue)
              }
            }
            this.addKeywordInputVisible = false
            this.addKeywordInputValue = ''
          }
        }
    }
</script>

<style scoped>
  @import "./../../assets/css/page/indexObserve.css";
</style>
