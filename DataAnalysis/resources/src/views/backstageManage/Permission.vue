<template>
    <page-wrap>
        <!-- 搜索栏 -->
        <search-wrap>
            <el-form ref="form" :model="searchData" label-width="100px" size="mini">
                <el-row :gutter="21">
                    <el-col :span="8">
                        <div class="bg">
                            <el-form-item label="用户组编号：">
                                <el-input v-model="searchData.ID" placeholder="请输入内容"></el-input>
                            </el-form-item>
                        </div>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label-width="0">
                            <el-button type="primary" @click="onSubmit">查询</el-button>
                            <el-button type="danger" @click="onClear">清空</el-button>
                            <el-button type="success" @click="openAddRoleDialog = true" :disabled=!buttonControl.rolesadd>添加</el-button>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
        </search-wrap>

        <!-- 表格 -->
        <table-wrap>
            <el-table
                    ref="premission_table"
                    v-loading="loading"
                    key="premission_table"
                    :data="tableData"
                    size="mini"
                    :height="tabTableHeight">
                <el-table-column
                        label="用户组编号"
                        prop="ID">
                </el-table-column>
                <el-table-column
                        label="用户组名"
                        prop="Description">
                </el-table-column>
                <el-table-column label="操作" width="200px" header-align="center">
                    <template slot-scope="scope">
                        <el-button type="text" plain size="mini" icon="el-icon-edit"
                                   @click="handleEdit(scope.$index, scope.row)" :disabled=!buttonControl.rolesedit>编辑
                        </el-button>
                        <el-button type="text" plain size="mini" icon="el-icon-delete"
                                   @click="handleDelete(scope.$index, scope.row)" :disabled=!buttonControl.rolesdelete>删除
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

        <el-dialog
                width="350px"
                title="添加用户组"
                :visible.sync="openAddRoleDialog"
                @close="handleCloseRoleDialog">

            <el-form :model="operateData">
                <el-form-item label="用户组名">
                    <el-input v-model="operateData.desc" size="mini"></el-input>
                </el-form-item>
            </el-form>

            <div slot="footer">
                <el-button size="mini" @click="handleCloseRoleDialog">取 消</el-button>
                <el-button size="mini" type="primary" @click="handleConfirmRoleDialog">确 定</el-button>
            </div>
        </el-dialog>

        <el-dialog
                width="650px"
                title="编辑用户组"
                :visible.sync="openEditRoleDialog"
                @close="handleCloseRoleDialog">

            <el-form :model="editRoleForm" size="small" label-width="100px">
              <el-row :gutter="21">
                  <el-col :span="12">
                    <el-form-item label="用户组名">
                        <el-input v-model="editRoleForm.desc"></el-input>
                    </el-form-item>
                    <el-form-item label="关键词任务数">
                        <el-input v-model="editRoleForm.num"></el-input>
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                        <el-form-item label="分配权限">

                            <el-tree
                                ref="role_tree"
                                :data="roleOptions"
                                show-checkbox
                                node-key="id"
                                @check-change="handleRoleTreeChange"
                                :render-content="renderContent"
                                :default-checked-keys="editRoleForm.selected">
                            </el-tree>
                        </el-form-item>
                  </el-col>
              </el-row>

            </el-form>
            <div slot="footer">
                <el-button size="mini" @click="handleCloseRoleDialog">取 消</el-button>
                <el-button size="mini" type="primary" @click="handleConfirmRoleEdit">确 定</el-button>
            </div>
        </el-dialog>
        <permission-choose-dp :handle="addDialog" :dp_id="select_dp_id" @callback="onChooseCallback"/>
    </page-wrap>
</template>

<script>
    import PageWrap from './../../components/pageStructure/PageWrap.vue'
    import SearchWrap from './../../components/pageStructure/SearchWrap.vue'
    import TableWrap from './../../components/pageStructure/TableWrap.vue'
    import paginationWrap from './../../components/pageStructure/paginationWrap.vue'
    import NewPagination from './../../components/base/NewPagination.vue'
    import PermissionChooseDp from './PermissionChooseDp.vue'
    import {mapState} from 'vuex'
    export default {
        components: {
            PageWrap, SearchWrap, TableWrap, paginationWrap, NewPagination, PermissionChooseDp
        },
        data() {
            return {
                searchData: {
                    ID: '',
                },
                tableData: [],
                buttonControl:[],
                operateData: {
                    roleSign: '',
                    desc: ''
                },
                paginationData: {},
                addDialog: false,
                openAddRoleDialog: false,
                openEditRoleDialog: false,
                editRoleForm:{
                    id: '',
                    title: '',
                    desc: '',
                    num:'',
                    selected: [],
                    dp_id:{}
                },
                select_dp_id: [],
                on_click_dp:'',
                roleOptions: [],
//                dpOptions:{},
                loading: false
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
                this.searchData.ID = '';
            },
            showPaginationData(data) {
                this.paginationData.pageSizes = data.pageSizes
                this.paginationData.pageSize = data.pageSize
                this.paginationData.currentPage = data.currentPage
            },
            onSubmit: function (action) {
                let that = this
                if (action !== 'changeCurrentPage') {
                    that.paginationData.currentPage = 1
                }
                if (action !== 'changeSort' && action !== 'changeCurrentPage') {
                    that.$refs.premission_table.clearSort()
                }
                that.loading = true
                this.$ajax.post('roles/index', {search: this.searchData, page: this.paginationData})
                    .then(function (res) {
                        if (res.data !== undefined) {
                            that.tableData = res.data.tableData
                            that.paginationData.total = res.data.total
                            that.buttonControl = res.data.buttonControl
//                            that.dpOptions=res.data.dp_option;
                        }
                        that.loading = false
                    })
                    .catch(function (err) {
                    })
            },
            onClear() {
                this.initSearchData()
                this.onSubmit()
            },

            showSizeChange(val) {
                this.paginationData.pageSize = val
                this.onSubmit()
            },
            showCurrentChange(val) {
                this.paginationData.currentPage = val
                this.onSubmit('changeCurrentPage')
            },
            handleCloseRoleDialog() {
                this.openAddRoleDialog = false;
                this.openEditRoleDialog = false
            },
            handleConfirmRoleDialog() {
                let that = this;
                this.$ajax.post('roles/add', {formData: this.operateData})
                    .then(function (res) {
                        if(res.data.status == 200){
                            that.$message({
                                message: res.data.msg,
                                type: 'success'
                            });
                            that.operateData.roleSign='';
                            that.operateData.desc='';
                            that.openAddRoleDialog = false;
                            that.onSubmit();
                        }
                        if(res.data.status == 400){
                            that.$message({
                                message: res.data.msg,
                                type: 'error'
                            });
                        }
                    })
                    .catch(function (err) {
                    })
            },
            handleDelete (index, row) {
                let that = this;
                this.$confirm('确定删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.$ajax.post('/roles/delete', {id:row["ID"]})
                        .then(function (res) {
                            if (res.status === 200) {
                                that.$message({
                                    message: res.data,
                                    type: 'success'
                                });
                                that.onSubmit();
                            }else{
                                that.$message({
                                    message: res.data,
                                    type: 'error'
                                });
                            }
                        })
                        .catch(function() {
                        })
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },
            handleEdit (index, row) {
                let that = this
                that.openEditRoleDialog= true;
                that.editRoleForm.id = row['ID'];
                that.editRoleForm.title = row["Title"];
                that.editRoleForm.desc = row["Description"];
                that.editRoleForm.num = row["KeywordNum"];
                this.$ajax.post('roles/edit', {type: 1,id:row['ID']})
                    .then(function (res) {
                        that.roleOptions=res.data.options;
                        that.editRoleForm.selected = res.data.selected
                        if (res.data.dp_id.length !== 0) {
                            that.editRoleForm.dp_id = res.data.dp_id
                        }
                    })
            },
            handleConfirmRoleEdit() {
                let that = this;
                this.$ajax.post('roles/edit', {table: that.editRoleForm})
                    .then(function (res) {
                        that.$message({
                            message: res.data.msg,
                            type: res.data.type
                        });
                        if (res.data.type === 'success'){
                            that.onSubmit();
                            that.openEditRoleDialog=false
                        }
                    })
                    .catch(function (err) {
                    })
            },
            onChooseCallback :function(data){
                let keyName = 'range_' + this.on_click_dp
                this.editRoleForm.selected.push(this.on_click_dp)
                this.$refs.role_tree.setCheckedKeys(this.editRoleForm.selected)
                this.editRoleForm.dp_id[keyName] = data
            },
            renderContent(h, { node, data, store }) {
                let that = this;
                return h('span', [
                    h('div', [
                        h('span', {
                            domProps: {innerHTML:data.label},
                            style: {
                                fontSize: '13px'
                            }
                        }),
                        h('span', [
                                h('i', {
                                    on: {click:()=>that.append(data)},
                                    class: data.type !== undefined && data.type === 1 ? 'el-icon-circle-plus' : '',
                                    style: {
                                        color: '#88d0e2',
                                        margin: '5px 10px',
                                        fontSize: '15px',
                                    }
                                })
                            ]
                        )
                    ])
                ])

            },
            append(data) {
                this.handleRoleTreeChange(data)
                this.select_dp_id = this.editRoleForm.dp_id['range_' + data.id] !== undefined ? this.editRoleForm.dp_id['range_' + data.id] :[]
                this.on_click_dp = data.id
                this.addDialog = !this.addDialog
            },
            handleRoleTreeChange (data, checked, indeterminate) {
              if (checked) {
                if (data.children !== undefined && data.children.length > 0) {
                  data.children.forEach((value, key) => {
                    if (this.editRoleForm.selected.indexOf(value.id) === -1) {
                      this.editRoleForm.selected.push(value.id)
                    }
                  })
                } else {
                  if (this.editRoleForm.selected.indexOf(data.id) === -1) {
                    this.editRoleForm.selected.push(data.id)
                  }
                }
              } else {
                let index = this.editRoleForm.selected.indexOf(data.id)
                if (index > -1) {
                  this.editRoleForm.selected.splice(index, 1)
                }
              }
//              console.log(this.editRoleForm.selected);
            }
        }
    }
</script>
