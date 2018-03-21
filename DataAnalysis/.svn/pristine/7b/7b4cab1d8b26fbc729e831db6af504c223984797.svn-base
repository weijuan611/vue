<template>
    <page-wrap>
        <!-- 搜索栏 -->
        <search-wrap>
            <el-form ref="form" :model="searchData" label-width="100px" size="mini">
                <el-row :gutter="20">
                    <el-col :span="7">
                        <div class="bg">
                            <el-form-item label="违禁词：">
                                <el-input v-model="searchData.word" placeholder="请输入需要查找的违禁词"></el-input>
                            </el-form-item>
                        </div>
                    </el-col>
                    <el-col :span="7">
                        <div class="bg">
                            <el-form-item label="添加人：">
                                <el-input v-model="searchData.add_user_name" placeholder="请输入内容"></el-input>
                            </el-form-item>
                        </div>
                    </el-col>

                    <el-col :span="10">
                        <div class="bg">
                            <el-form-item label="添加时间：">
                                <el-date-picker
                                        v-model="searchData.add_time"
                                        type="daterange"
                                        value-format="yyyy-MM-dd"
                                        range-separator="至"
                                        start-placeholder="开始日期"
                                        end-placeholder="结束日期">
                                </el-date-picker>
                            </el-form-item>
                        </div>
                    </el-col>

                  </el-row>
                <el-row :gutter="22">
                    <el-col :span="7">
                        <div class="bg">
                            <el-form-item label="状态：">
                                <el-select v-model="searchData.status" placeholder="请选择" value="">
                                    <el-option
                                            v-for="item in status_arr"
                                            :key="item.value"
                                            :label="item.label"
                                            :value="item.value">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                    </el-col>

                    <el-col :span="17">
                        <el-form-item label-width="0">
                            <el-button type="primary" @click="onSubmit">查询</el-button>
                            <el-button type="danger" @click="onClear">清空</el-button>
                            <el-button type="success" @click="addDialog = !addDialog">添加</el-button>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
        </search-wrap>



        <!-- 表格 -->
        <table-wrap>
            <div class="withTableHeader" style="width: 99.8%;">
                <el-button type="primary" plain size="mini" @click="denyWordsBatchChangeType('1')" :disabled="!buttonControl.material_denywordsmaterial_changetype">批量激活</el-button>
                <el-button type="danger" plain size="mini" @click="denyWordsBatchChangeType('0')" :disabled="!buttonControl.material_denywordsmaterial_changetype">批量停用</el-button>
            </div>
            <el-table
                    ref="materialsourcetable"
                    v-loading="loading"
                    :data="tableData"
                    size="mini"
                    @select="selectTableRow"
                    @select-all="selectTableAllRow"
                    @sort-change="tableSortChange"
                    :height="tabTableHeight">
                <el-table-column fixed="left" type="selection" width="55px"></el-table-column>
                <el-table-column
                        label="关键词"
                        sortable
                        prop="word">
                </el-table-column>
                <el-table-column
                    label="状态"
                    width="80px"
                    prop="status">
                    <template slot-scope="props">
                        <span v-show="props.row.status == 1" class="check_green">正常</span>
                        <span v-show="props.row.status == 0" class="check_red">已停用</span>
                    </template>
                </el-table-column>
                <el-table-column
                        label="添加人"
                        width="100px"
                        prop="add_user_name">
                </el-table-column>
                <el-table-column
                        label="添加人部门"
                        width="100px"
                        prop="add_dp_name">
                </el-table-column>
                <el-table-column
                        label="添加时间"
                        width="140px"
                        prop="add_time">
                </el-table-column>
                <el-table-column
                        label="更新人"
                        width="100px"
                        prop="update_user_name">
                </el-table-column>
                <el-table-column
                        label="更新部门"
                        width="100px"
                        prop="update_dp_name">
                </el-table-column>
                <el-table-column
                        label="更新时间"
                        width="140px"
                        prop="update_time">
                </el-table-column>
                <el-table-column
                        label="备注"
                        prop="menu">
                </el-table-column>
                <el-table-column label="操作" header-align="center" width="110px">
                    <template slot-scope="scope">
                        <el-button type="text" plain size="mini" icon="el-icon-delete"
                                   @click="onDelete(scope.row,0)" v-show="scope.row.status == 1" :disabled="!buttonControl.material_denywordsmaterial_changetype">停用
                        </el-button>
                        <el-button type="text" plain size="mini" icon="el-icon-setting"
                                   @click="onDelete(scope.row,1)" v-show="scope.row.status == 0" :disabled="!buttonControl.material_denywordsmaterial_changetype">激活
                        </el-button>
                    </template>
                </el-table-column>
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
        <denywords-add :handle="addDialog" @callback="onAddCallback"/>
    </page-wrap>
</template>

<script>
    import PageWrap from './../../components/pageStructure/PageWrap.vue'
    import SearchWrap from './../../components/pageStructure/SearchWrap.vue'
    import TableWrap from './../../components/pageStructure/TableWrap.vue'
    import paginationWrap from './../../components/pageStructure/paginationWrap.vue'
    import NewPagination from './../../components/base/NewPagination.vue'
    import DenywordsAdd from './DenywordsAdd.vue'
    import {mapState} from 'vuex'
    import { getDates }  from './../../assets/js/baseFunc/baseSelfFunc'

    export default {
        components: {
            PageWrap, SearchWrap, TableWrap, paginationWrap, NewPagination,DenywordsAdd
        },
        data() {
            return {
                buttonControl:{},
                searchData: {
                    word: '',
                    add_user_id:'',
                    status:'',
                    add_time:[],
                },
                tableData: [],
                checkMany :[],
                addDialog:false,
                loading: false,
                openEditmaterial: false,
                paginationData: {},
                status_arr: [
                    {value: 0, label: '停用'},
                    {value: 1, label: '正常'},
                ],
            }
        },
        computed: {
            ...mapState('navTabs', [
                'tabTableHeight'
            ])
        },
        mounted() {
            this.initSearchData()
            this.onSubmit()
        },
        methods: {
            initSearchData() {
                this.searchData = {
                    word: '',
                    add_user_id:'',
                    status:'',
                    add_time:[],
                }
            },
            onClear() {
                this.initSearchData()
            },
            onAddCallback:function (data) {
                if (data === "true") {
                    this.onSubmit()
                }
            },
            onSubmit(action = '') {
                let that = this
                if (action !== 'changeCurrentPage') {
                    that.paginationData.currentPage = 1
                }
                if (action !== 'changeSort' && action !== 'changeCurrentPage') {
                    that.paginationData.prop = 'add_time'
                    that.paginationData.order = "descending"
                }
                if (!that.loading) {
                    this.$ajax.post('/material_denywords/init', {
                        search: that.searchData,
                        paginate: that.paginationData
                    })
                        .then(function (res) {
                            if (res.data !== undefined) {
                                that.tableData = res.data.data
                                that.paginationData.total = res.data.total
                                that.loading = false
                                that.buttonControl = res.data.buttonControl
                            }
                        })
                        .catch(function (err) {
                            that.$message('网络错误！请联系管理员')
                            that.loading = false
                        })
                }
            },
            tableSortChange(val) {
                this.paginationData.prop = val.prop
                this.paginationData.order = val.order
                this.onSubmit('changeSort')
            },
            showPaginationData(data) {
                this.paginationData.pageSizes = data.pageSizes
                this.paginationData.pageSize = data.pageSize
                this.paginationData.currentPage = data.currentPage
            },
            showSizeChange(val) {
                this.paginationData.pageSize = val
                this.onSubmit()
            },
            showCurrentChange(val) {
                this.paginationData.currentPage = val
                this.onSubmit('changeCurrentPage')
            },
            selectTableRow(selection) {
                this.checkMany = selection.map(m => m.dw_id)
            },
            selectTableAllRow(selection) {
                this.checkMany = selection.map(m => m.dw_id)
            },
            onDelete(row,type) {
                let that = this
                let message = ''
                if (type === 0) {
                    message = '是否停用该记录'
                } else {
                    message = '是否激活该记录'
                }
                this.$confirm(message, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.$ajax.post('/material_denywords/material_changetype', {id: row.dw_id,type:type})
                        .then(function (res) {
                            if (res.data.type) {
                                that.openEditmaterial = false
                                that.onSubmit()
                                that.$message({
                                    type: 'success',
                                    message: res.data.info
                                })
                            } else {
                                that.$message({type: 'error', message: res.data.info})
                            }
                        })
                        .catch(function (err) {
                            that.$message('网络错误！请联系管理员')
                            that.loading = false
                        })
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消操作'
                    });
                });
            },
            denyWordsBatchChangeType (type) {
                let that = this;
                this.$ajax.post('material_denywords/material_changetype', {type:type,id:that.checkMany})
                    .then(function (res) {
                        if (res.data.type){
                            that.openEditmaterial = false
                            that.onSubmit()
                            that.$message({
                                type: 'success',
                                message: res.data.info
                            })
                        } else {
                            that.$message({type: 'error', message: res.data.info})
                        }
                    })
            },
        }
    }
</script>
