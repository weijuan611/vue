<template>
    <!-- dialog选择用户开始 -->
    <el-dialog
            width="30%"
            title="选择地区"
            :modal="false"
            @close="show = false"
            :visible="show">
        <el-form label-width="80px" ref="common_dialog_area_form"  size="mini">
            <input type="text" name="none" style="display: none;"/>
            <el-form-item label="地区名:">
                <el-input v-model="search.area_name" @keyup.enter.native="searchSubmit(null)"><el-button slot="append" icon="el-icon-search" @click="searchSubmit(null)"/></el-input>
            </el-form-item>
        </el-form>
        <el-table
                ref="common_dialog_area"
                :data="table"
                v-loading="loading"
                tooltip-effect="dark"
                size="mini"
                highlight-current-row
                @current-change="handleSelect">
            <el-table-column label="地区名" prop="area_name"></el-table-column>
        </el-table>
        <Pagination :data="pagination" @submit="searchSubmit" layout="prev, pager, next"/>
        <div slot="footer">
            <el-button size="mini" @click="show = false">取 消</el-button>
            <el-button size="mini" type="primary" @click="handleConfirm">确 定</el-button>
        </div>
    </el-dialog>
</template>
<script>
    import Pagination from './../pageStructure/Pagination.vue'
    export default {
        components:{
          Pagination
        },
        props:{
            handle:{
                type:Boolean,
                default:false
            }
        },
        watch:{
            handle:function (data,old) {
                this.show= true;
            }
        },
        mounted(){
            this.searchSubmit();
        },
        data(){
            return {
                show:false,
                loading:false,
                pagination:{currentPage:1,pageSize:5,total:0},
                table:[
                    {area_id:0,area_name:''}
                ],
                search:{area_name:''},
                multipleSelection:[],
            }
        },
        methods:{
            searchSubmit:function (pagination=null) {
                let that = this
                if(pagination !== null){
                    that.pagination = pagination;
                }else{
                    that.pagination.currentPage = 1;
                }
                if(this.loading){
                    return
                }
                this.loading = false
                this.$ajax.post('common/dialog/area', {search: that.search, paginate: that.pagination})
                    .then(function (res) {
                        if (res.data !== undefined) {
                            that.table = res.data.data
                            that.pagination.total = res.data.total
                            that.loading = false
                        }
                    })
                    .catch(function (err) {
                        that.$message('网络错误！请联系管理员')
                        that.loading = false
                    })
            },
            handleSelect:function (val) {
                this.multipleSelection = val;
            },
            handleConfirm:function(){
                this.show = false
                this.$emit('callback',this.multipleSelection)
            }
        }
    }
</script>