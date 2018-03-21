<template>
    <!-- dialog选择用户开始 -->
    <el-dialog
            width="30%"
            title="添加违禁词"
            :modal="false"
            @close="show = false"
            :visible="show">
        <el-form label-width="80px" ref="common_dialog_user_form"  size="mini">
            <el-form-item label="违禁词:">
                <el-input v-model="search.word" ></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer">
            <el-button size="mini" @click="show = false">取 消</el-button>
            <el-button size="mini" type="primary" @click="onSubmit">确 定</el-button>
        </div>
    </el-dialog>
</template>
<script>
    export default {
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
        data(){
            return {
                show:false,
                loading:false,
                search:{word:''},
            }
        },
        methods:{
            onSubmit:function () {
                let that = this
                if(this.loading){
                    return
                }
                this.loading = false
                this.$ajax.post('material_denywords/material_addDenyword', {search: that.search})
                    .then(function (res) {
                        if (res.data !== undefined) {
                            that.$message({
                                type: res.data.type,
                                message: res.data.info
                            })
                            that.show = false
                            that.$emit('callback','true')
                        }
                    })
                    .catch(function (err) {
                        that.$message('网络错误！请联系管理员')
                        that.loading = false
                    })
            },
        }
    }
</script>