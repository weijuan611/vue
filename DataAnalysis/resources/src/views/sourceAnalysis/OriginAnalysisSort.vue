<template>
    <page-wrap>

        <!-- 标题栏 -->
        <page-title title="来源分析-来源域名升降" :date="searchData.dateTime" :date2="searchData.dateTime2" />

        <!-- 搜索栏 -->
        <search-wrap>
            <el-form ref="form" :model="searchData" size="mini">
                <el-form-item>
                    <el-date-picker
                            v-model="searchData.dateTime"
                            @change="changeDate"
                            type="daterange"
                            value-format="yyyy-MM-dd"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期">
                    </el-date-picker>
                    <span>&nbsp;&nbsp;对比&nbsp;&nbsp;</span>
                    <el-date-picker
                            v-model="searchData.dateTime2"
                            type="daterange"
                            @change="changeDate"
                            value-format="yyyy-MM-dd"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期">
                    </el-date-picker>
                    <el-button type="success" @click="onExportAll" style="display: none;"
                    :disabled=!buttonControl.originanalysissortexport>导出表格</el-button>
                </el-form-item>
            </el-form>
        </search-wrap>


        <!-- 其它的都放在这儿 -->
        <other-wrap>
            <div class="rect">
                <p class="colorBall1">访问次数</p>
                <ul class="originDataSortDetail">
                    <template v-for="(item, index) in total.total">
                        <li>
                            <p>{{item.title}}</p>
                            <p :style="{color: index === 2 ? total.type.type === 'up' ? 'red' : total.type.type === 'down' ? 'green' : '#888' : ''}">{{item.number}}</p>
                        </li>
                    </template>
                </ul>
            </div>
        </other-wrap>


        <!-- 表格 -->
        <table-wrap style="position: relative;">
            <div class="table-header-slot">
              <a class="font_red" @click="showChangeType('up')">+ 升</a>
              <a class="font_green" @click="showChangeType('down')">- 降</a>
              <a class="font_grey" @click="showChangeType('equal')"> 平</a>
              <a class="font_blue" @click="showChangeType('all')"> 全部</a>
            </div>
            <el-table
                    ref="table"
                    v-loading="loading"
                    :data="tableData"
                    size="mini"
                    sortable="custom"
                    @sort-change="tableSortChange"
                    :cell-style="setCellStyle"
                    :height="tabTableHeight">
                <el-table-column prop="domain" label="来路域名">
                  <template slot-scope="scope">
                    <a :href="'http://'+scope.row.domain" target="_blank" :title="scope.row.domain">{{scope.row.domain}}</a>
                  </template>
                </el-table-column>
                <el-table-column prop="one_num" :label="showTime1">
                  <template slot-scope="scope">
                      <a @click="clickDomain(scope)" :title="scope.row.one_num">{{scope.row.one_num}}</a>
                  </template>
                </el-table-column>
                <el-table-column prop="two_num" :label="showTime2">
                  <template slot-scope="scope">
                      <a @click="clickDomain(scope)" :title="scope.row.two_num">{{scope.row.two_num}}</a>
                  </template>
                </el-table-column>
                <el-table-column prop="sort" :render-header="renderTableHeader"></el-table-column>
            </el-table>
        </table-wrap>


        <!-- 分页 -->
        <pagination-wrap>
            <new-pagination
                    :total="paginationData.total"
                    :propCurrentPage="paginationData.currentPage"
                    @initPaginationData="showPaginationData"
                    @handleSizeChange="showSizeChange"
                    @handleCurrentChange="showCurrentChange"
            />
        </pagination-wrap>

    </page-wrap>
</template>


<script>
    import PageWrap from './../../components/pageStructure/PageWrap.vue'
    import OtherWrap from './../../components/pageStructure/OtherWrap.vue'
    import SearchWrap from './../../components/pageStructure/SearchWrap.vue'
    import TableWrap from './../../components/pageStructure/TableWrap.vue'
    import paginationWrap from './../../components/pageStructure/paginationWrap.vue'
    import NewPagination from './../../components/base/NewPagination.vue'
    import PageTitle from './../../components/pageStructure/PageTitle.vue'

    import { getDates }  from './../../assets/js/baseFunc/baseSelfFunc'
    import { mapState, mapMutations } from 'vuex'

    export default {
        components: {
            PageWrap, SearchWrap, OtherWrap, TableWrap, paginationWrap, PageTitle, NewPagination
        },
        data () {
            return {
                searchData: {
                    dateTime: [getDates('yesterday'), getDates('yesterday')],
                    dateTime2: [getDates('daybefore'), getDates('daybefore')],
                    urldetail: "",
                    sort:""
                },
                showTime1: "",
                showTime2: "",
                buttonControl:[],
                total: [
                    {
                        title: '2017-12-14 至 2017-12-14',
                        number: 0
                    },
                    {
                        title: '2017-12-17 至 2017-12-23',
                        number: 0
                    },
                    {
                        title: '变化情况',
                        number: "+0(+0%)"
                    }
                ],
                tableData: [],
                paginationData: {
                    total: 0
                },
                dayMark: '昨天',
                loading: false
            }
        },
        mounted () {
            this.init()
        },
        computed: {
            ...mapState('navTabs',[
                'tabTableHeight'
            ])
        },
        methods: {
            initSearchData () {
                this.dayMark = '昨天'
                this.searchData.dateTime = [getDates('yesterday'), getDates('yesterday')]
                this.searchData.dateTime2 = [getDates('daybefore'), getDates('daybefore')]
                if (this.searchData.dateTime[0] === this.searchData.dateTime[1]){
                    this.showTime1 = this.searchData.dateTime[0]
                } else{
                    this.showTime1 = this.searchData.dateTime[0] + " 至 " + this.searchData.dateTime[1]
                }
                if (this.searchData.dateTime2[0] === this.searchData.dateTime2[1]){
                    this.showTime2 = this.searchData.dateTime2[0]
                } else {
                    this.showTime2 = this.searchData.dateTime2[0] + " 至 " + this.searchData.dateTime2[1]
                }
                this.onSubmit()
            },
            init () {
                this.initSearchData()
            },
            tableSortChange (val) {
                this.paginationData.prop = val.prop
                this.paginationData.order = val.order
                this.onSubmit('changeSort')
            },
            onSubmit (action,type="") {
                let that = this
                that.loading = true
                if (action !== 'changeCurrentPage') {
                  that.paginationData.currentPage = 1
                }
                if (action !== 'changeSort' && action !== 'changeCurrentPage') {
                  that.$refs.table.clearSort()
                  this.paginationData.prop = ''
                  this.paginationData.order = 'descending'
                }
                if (type !== "") {
                    that.searchData.sort = type
                } else {
                    that.searchData.sort = ""
                }
                that.$ajax.post('/originanalysissort/index', {"searchData":that.searchData,"paginate":that.paginationData})
                    .then(function (res) {
                        if (res.data) {
                            that.tableData = res.data.tableData
                            that.total = res.data.total
                            that.paginationData.total = res.data.page
                            that.buttonControl = res.data.buttonControl
                        }
                        that.loading = false
                    })
                    .catch(function(err) {
                    })
            },
            onClear () {
                this.initSearchData()
            },
            changeDate(val) {
              if (val !== null) {
                if (this.searchData.dateTime[0] === this.searchData.dateTime[1]) {
                    this.showTime1 = this.searchData.dateTime[0]
                } else{
                    this.showTime1 = this.searchData.dateTime[0] + " 至 " + this.searchData.dateTime[1]
                }
                if (this.searchData.dateTime2[0] === this.searchData.dateTime2[1]) {
                    this.showTime2 = this.searchData.dateTime2[0]
                } else {
                    this.showTime2 = this.searchData.dateTime2[0] + " 至 " + this.searchData.dateTime2[1]
                }
              }
              this.onSubmit()
            },
            onExportAll () {
                let that = this
                that.loading = true
                that.$ajax.post('/keyword/export', {"type":"1"})
                    .then(function (res) {
                        if (res.data) {
                            window.location = 'index.php/originanalysissort/export?start_time2=' + that.searchData.dateTime2[0]
                                + '&end_time2=' + that.searchData.dateTime2[1]
                                + '&start_time1=' + that.searchData.dateTime[0]
                                + '&end_time1=' + that.searchData.dateTime[1]
                                + '&sort=' + that.searchData.sort
                                + '&prop=' + that.paginationData.prop
                                + '&order=' + that.paginationData.order;
                        }
                    })
                    .catch(function(err) {
                    })
            },
            showPaginationData (data) {
                this.paginationData.pageSizes = data.pageSizes
                this.paginationData.pageSize = data.pageSize
                this.paginationData.currentPage = data.currentPage
                this.paginationData.total = data.total
            },
            showSizeChange (val) {
                this.paginationData.pageSize = val
                this.onSubmit()
            },
            showCurrentChange (val) {
                this.paginationData.currentPage = val
                this.onSubmit('changeCurrentPage')
            },
            setCellStyle (val) {
              if (val.columnIndex == 3) {
                if (val.row.type === 'up') {
                  return { color : 'red', fontWeight: 'bold' }
                } else if (val.row.type === 'down') {
                  return { color : 'green', fontWeight: 'bold' }
                } else {
                  return { color : '#777', fontWeight: 'bold' }
                }
              }
            },
            ...mapMutations('navTabs', ['addTab', 'func_domain_to_visitDetail']),
            clickDomain (val) {
                let val_number = "";
                let val_date = ""
                if (val.column.property === "one_num") {
                    val_number = val.row.one_num
                    val_date = this.searchData.dateTime
                }else {
                    val_number = val.row.two_num
                    val_date = this.searchData.dateTime2
                }
              let obj = {
                date: val_date,
                number: val_number,
                url: {
                  way: 'from-zdy',
                  value: val.row.domain
                }
              }
              this.addTab({name: 'flowAnalysis/VisitDetail', title: '访问明细'})
              this.func_domain_to_visitDetail(obj)
            },
            showChangeType (type) {
                this.onSubmit("",type)
            },
            renderTableHeader (createElement, { column, $index }) {
              return ''
              // return <a>你好</a>
              // return
              //   <div>
              //     <a @click="showChangeType('up')"></a>
              //     <a @click="showChangeType('down')">- 降</a>
              //     <a @click="showChangeType('equal')"> 平</a>
              //     <a @click="showChangeType('all')"> 全部</a>
              //     <span>{{scope.row.sort}}</span>
              //   </div>
              //
              // return createElement (
              //   'div',
              //   [
              //     createElement('a', ['+ 升'], {
              //       on: {
              //         click: () => {
              //           this.showChangeType('type')
              //         }
              //       }
              //     })
              //   ]
              // )
              // console.log('h', h)
              // console.log('column', column)
              // console.log('$index', $index)
            }
        }
    }
</script>

<style scoped>
    @import "./../../assets/css/page/OriginAnalysisSort.css";
</style>
