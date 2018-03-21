<template>
    <el-dialog title="历史记录" :visible="dialogArticlesLogShow" @close="dialogArticlesLogShow = false" :modal="false" width="30%">
        <el-table
                :data="form"
                size="mini"
                :height="170">
            <el-table-column prop="title" label="标题"></el-table-column>
            <el-table-column prop="edit_time" label="时间"></el-table-column>
            <el-table-column prop="menu" label="操作"></el-table-column>
        </el-table>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogArticlesLogShow = false">关  闭</el-button>
        </div>
    </el-dialog>
</template>
<script>
    export default {
        components:{
        },
        props:{
            handle:{
                type:Boolean,
                default:false,
            },
            keyword: {
                type: Number,
                default: 0
            }
        },
        watch:{
            handle:function (data,old) {
                this.dialogArticlesLogShow = true
            },
            keyword () {
                this.search.ka_id = this.keyword
                this.onSubmit()
            },
        },
        data(){
            return {
                dialogArticlesLogShow:false,
                form:[],
                search:{
                    ka_id:""
                },
            }
        },
        methods:{
            onSubmit() {
                let that = this
                this.$ajax.post('/material_articles/loginfo', {search: that.search})
                    .then(function (res) {
                        if (res.data !== 'undefined') {
                            that.form = res.data.data
                        }
                    })
                    .catch(function (err) {
                        that.$message('网络错误！请联系管理员')
                    })
            }
        }
    }
</script>