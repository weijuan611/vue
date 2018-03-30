<template>
    <!-- dialog选择用户开始 -->
    <el-dialog
            width="30%"
            title="选择课程"
            :modal="false"
            @close="show = false"
            :visible="show">
        <el-form label-width="80px" ref="common_dialog_course_form"  size="mini">
            <input type="text" name="none" style="display: none;"/>
            <el-form-item label="课程号:">
                <el-input v-model="search_data.courseid" @keyup.enter.native="searchSubmit(null)"><el-button slot="append" icon="el-icon-search" @click="searchSubmit(null)"/></el-input>
            </el-form-item>
        </el-form>
        <el-table
                ref="common_dialog_courses"
                :data="table"
                v-loading="loading"
                tooltip-effect="dark"
                size="mini"
                highlight-current-row
                @current-change="handleSelect">
            <el-table-column label="课程编号" prop="Id"></el-table-column>
            <el-table-column label="课程名称" prop="Name"></el-table-column>
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
            },
            search:{
                type:Object,
                default:function () {
                    return {
                        areaId:0,
                        keyword:'',
                        category:'',
                        courseid:0
                    }
                }
            }
        },
        watch:{
            handle:function (data,old) {
                this.show= true;
            },
            search:function (data,old) {
                this.search_data = {
                    areaId:data.areaId,
                    keyword:data.keyword,
                    category:data.category,
                    courseid:0
                };
                this.searchSubmit();
            }
        },
        mounted(){
//            this.searchSubmit();
        },
        data(){
            return {
                show:false,
                loading:false,
                search_data:{
                    areaId:0,
                    keyword:'',
                    category:'',
                    courseid:0
                },
                pagination:{currentPage:1,pageSize:5,total:0},
                table:[
                    {Id:'',Name:'',LoginId:0}
                ],
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
                this.$ajax.post('common/dialog/courses', {search: that.search_data, paginate: that.pagination})
                    .then(function (res) {
                        if (res.data !== undefined) {
                            that.table = res.data.data
                            that.pagination.total = res.data.total
                            that.loading = false
                        }
                    })
                    .catch(function (err) {
                        that.table = [{Id:'',Name:'',LoginId:0}]
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