<template>
    <page-wrap>
        <!-- dialog -->
        <el-dialog title="添加账号" :visible.sync="addUser" width="30%" size="mini" center>
            <el-form size="small" :model="addUserForm"  ref="addUserForm">
                <input type="hidden" v-model="addUserForm.id" name="id">
                <el-form-item prop="baidu_name" label="账号名：" label-width="100px">
                    <el-input v-model="addUserForm.baidu_name" placeholder="账号名"></el-input>
                </el-form-item>
                <el-form-item prop="baidu_pwd" label="密码：" label-width="100px">
                    <el-input v-model="addUserForm.baidu_pwd" placeholder="密码" type="password"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="addUser = false">取 消</el-button>
                <el-button size="small" type="primary" @click="onAdd('addUserForm')">确 定</el-button>
            </div>
        </el-dialog>

        <!-- dialog -->
        <el-dialog title="输入验证码" :visible.sync="codeDialog" width="30%" size="mini" center>
            <el-form size="small">
                <el-form-item prop="codeImg" label="" label-width="100px">
                    <img :src="codeImg" class="img-rounded" height="100px" width="200px">
                </el-form-item>
                <el-form-item prop="code" label="验证码：" label-width="100px">
                    <el-input v-model="code" type="text" @keyup.enter.native="enterCode()"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="codeDialog = false">取 消</el-button>
                <el-button size="small" type="primary" @click="enterCode()">确 定</el-button>
            </div>
        </el-dialog>

        <!-- dialog -->
        <el-dialog title="登录图片" :visible.sync="imgDialog" center>
                <img :src="img" class="img-rounded" height="360px" width="640px">
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="imgDialog = false">取 消</el-button>
            </div>
        </el-dialog>

        <!-- 表格 -->
        <table-wrap>
            <div class="withTableHeader">
              <el-button type="primary" plain @click="handleEdit()" size="mini">添加账号</el-button>
            </div>
            <el-table
                    ref="table"
                    v-loading="loading"
                    key="account_table"
                    :data="tableData"
                    size="mini"
                    :height="tabTableHeight">
                <el-table-column
                        label="账号名称"
                        prop="baidu_name">
                </el-table-column>
                <el-table-column
                        label="添加时间"
                        prop="add_time">
                </el-table-column>
                <el-table-column
                        label="更新时间"
                        prop="update_time">
                </el-table-column>
                <el-table-column label="操作" width="260px" header-align="center">
                    <template slot-scope="scope">
                        <el-button type="text" plain size="mini" icon="el-icon-view"
                                   @click="handleInfo(scope.$index, scope.row)">查看
                        </el-button>
                        <el-button type="text" plain size="mini" icon="el-icon-edit"
                                   @click="handleEdit(scope.$index, scope.row)">编辑
                        </el-button>
                        <el-button type="text" plain size="mini" icon="el-icon-setting"
                                   @click="handleLogin(scope.$index, scope.row)">登录
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
    </page-wrap>
</template>

<script>
    import PageWrap from './../../components/pageStructure/PageWrap.vue'
    import SearchWrap from './../../components/pageStructure/SearchWrap.vue'
    import TableWrap from './../../components/pageStructure/TableWrap.vue'
    import paginationWrap from './../../components/pageStructure/paginationWrap.vue'
    import NewPagination from './../../components/base/NewPagination.vue'
    import {mapState} from 'vuex'

    export default {
        components: {
            PageWrap, SearchWrap, TableWrap, paginationWrap, NewPagination
        },
        data() {
            return {
                addUserForm: {
                    id:0,
                    baidu_name: '',
                    baidu_pwd: '',
                },
                tableData: [],
                paginationData: {},
                addUser: false,
                code:'',
                codeId:0,
                codeImg:'',
                codeDialog:false,
                loginImg:'',
                loginDialog:false,
                img:'',
                imgDialog:false,
                loading: false
            }
        },
        computed: {
            ...mapState('navTabs', [
                'tabTableHeight'
            ])
        },
        mounted() {
            this.onSubmit()
        },
        methods: {
            onSubmit(action) {
                let that = this
                if (action !== 'changeCurrentPage') {
                    that.paginationData.currentPage = 1
                }
                if (action !== 'changeSort' && action !== 'changeCurrentPage') {
                    that.$refs.table.clearSort()
                }
                that.loading = true
                this.$ajax.post('/user_account/index', {paginate: this.paginationData})
                    .then(function (res) {
                        if (res.data !== undefined) {
                            that.tableData = res.data.tableData
                            that.paginationData.total = res.data.total
                        }
                        that.loading = false
                    })
                    .catch(function (err) {
                    })
            },
            onAdd(formName) {
                const self = this;
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.$ajax.post('/user_account/add', self.addUserForm)
                            .then(function (res) {
                                if (res.status === 200) {
                                    self.$message({
                                        message: res.data,
                                        type: 'success'
                                    });
                                }
                                self.addUserForm.baidu_name = '';
                                self.addUserForm.baidu_pwd = '';
                                self.addUser = false;
                                self.onSubmit();
                            })
                            .catch(function (err) {
                                self.$message.error('网络错误，请刷新!');
                                self.addUserForm.baidu_name = '';
                                self.addUserForm.baidu_pwd = '';
                                self.addUser = false;
                            });
                    } else {
                        return false
                    }
                });
            },
            handleEdit(index=null,row=null){
                if(index === null||row === null){
                    this.addUser = true
                    this.addUserForm ={
                        id:0,
                        baidu_name: '',
                        baidu_pwd: '',
                    }
                }else{
                    let that = this
                    this.$ajax.get('/user_account/info?id='+row['id'])
                        .then(function (res) {
                            if (res.status === 200) {
                                that.addUserForm = res.data.info
                                that.addUser = true
                            } else {
                                that.$message({
                                    message: res.data,
                                    type: 'error'
                                });
                            }
                        }).catch(function (err) {
                        self.$message.error('网络错误，请重试!');
                    });
                }
            },
            handleInfo(index,row){
                let that = this
                that.img =''
                this.$ajax.get('/user_account/img?id='+row['id'])
                    .then(function (res) {
                        if (res.status === 200 ) {
                            that.img = res.data;
                            that.imgDialog = true;
                        } else {
                            that.$message({
                                message: res.data,
                                type: 'error'
                            });
                        }
                    })
                    .catch(function (err) {
                        self.$message.error('网络错误!请刷新');
                    })
            },
            handleLogin(index, row) {
                let that = this
                this.$ajax.get('/user_account/login?id='+row['id'])
                    .then(function (res) {
                        if (res.status === 200 && res.data.img !== 'false') {
                            that.codeId = res.data.id
                            that.codeImg = res.data.img
                            that.codeDialog = true
                        } else {
                            that.$message({
                                message: res.data.msg,
                                type: 'error'
                            });
                        }
                    })
                    .catch(function (err) {
                        self.$message.error('网络错误!请刷新');
                    })
            },
            enterCode(){
                let that = this
                this.$ajax.post('/user_account/code',{id:that.codeId,code:that.code})
                    .then(function (res) {
                        if (res.status === 200&&res.data.error === 0) {
                            that.$message({
                                message: res.data.msg,
                                type: 'success'
                            });
                            that.codeDialog = false
                        } else {
                            that.$message({
                                message: res.data.msg,
                                type: 'error'
                            });
                            that.codeDialog = false
                        }
                        that.code=''
                    })
                    .catch(function (err) {
                        self.$message.error('网络错误!请刷新');
                        that.codeDialog = false
                        that.code=''
                    })
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
            }
        }
    }
</script>
