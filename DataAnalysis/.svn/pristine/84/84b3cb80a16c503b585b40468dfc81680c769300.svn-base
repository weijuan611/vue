<template>
    <el-dialog
            width="30%"
            title="选择功能权限范围"
            :modal="false"
            @close="show = false"
            :visible="show">
        <el-form ref="task_add_form" :model="form" size="mini"  label-width="100px">
            <el-form-item label="权限范围">
                <el-tree
                        ref="permission_tree"
                        :data="dpOptions"
                        show-checkbox
                        node-key="id"
                        default-expand-all
                        @check-change="handleCheckChange"
                        :default-checked-keys="editRoleForm.dp_id">
                </el-tree>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="mini" @click="onCancel">取 消</el-button>
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
            },
            dp_id:{
                type:Array,
                default:[]
            }
        },
        watch:{
            handle() {
                this.show= true;
                this.ininData();
            },
            dp_id () {
                this.editRoleForm.dp_id= this.dp_id;
            }
        },
        data(){
            return {
                show:false,
                loading:false,
                form:{
                },
                dpOptions:[],
                editRoleForm:{
                    dp_id:[]
                },

            }
        },
        methods:{
            ininData () {
                let that = this
                this.$ajax.get('roles/getalldp').then(function (res) {
                    that.dpOptions = res.data
                }).catch(function () {
                    that.$message.error('网络错误,请刷新')
                })
            },
            handleCheckChange(data, checked, indeterminate) {
                if(checked){
                    if(data.children !== undefined){
                        return;
                    }
                    if(this.editRoleForm.dp_id.indexOf(data.id) === -1){
                        this.editRoleForm.dp_id.push(data.id)
                    }
                }else{
                    let i=  this.editRoleForm.dp_id.indexOf(data.id);
                    if(i !== -1){
                        this.editRoleForm.dp_id.splice(i,1)
                    }
                }
            },
            onSubmit() {
                this.show = false
                this.$emit('callback',this.editRoleForm.dp_id)
            },
            onCancel() {
                this.show = false
                this.$emit('callback',this.dp_id)
            }
        }
    }
</script>
