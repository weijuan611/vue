<template>
    <el-tree
            :data="data"
            :props="defaultProps"
            node-key="id"
            default-expand-all
            :render-content="renderContent">
    </el-tree>
</template>
<script>
    export default {
        data() {
            return {
                data: [],
                defaultProps: {
                    children: 'children',
                    label: 'label'
                }
            };
        },
        mounted() {
            this.init()
        },
        methods: {
            init(){
                let that = this
                this.$ajax.get('department/list').then(function (res) {
                    that.data = res.data
                }).catch(function () {
                    that.$message.error('网络错误,请刷新')
                })
            },
            append(data) {
                console.log(data);
                let that = this
                this.$prompt('部门名称', '', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(({ value }) => {
                    that.$ajax.post('department/add',{name:value,id:data.id}).then(function (res) {
                        let type='success'
                        if(res.data.error === 1){
                            type='error';
                        }else{
                            that.init()
                        }
                        that.$message({
                            type: type,
                            message: res.data.msg
                        });
                    })

                });
            },
            remove(data) {
                let that = this
                this.$confirm('您确定要删除此部门及其下子部门？', '警告', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    that.$ajax.get('department/delete?id='+data.id).then(function (res) {
                        let type='success'
                        if(res.data.error === 1){
                            type='error';
                        }else{
                            that.init()
                        }
                        that.$message({
                            type: type,
                            message: res.data.msg
                        });
                    })

                });
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
                        class: 'el-icon-circle-plus',
                        style: {
                          color: '#88d0e2',
                          margin: '5px 10px',
                          fontSize: '15px'
                        }
                      }),
                      h('i', {
                        on: {click:()=>that.remove(data)},
                        class: 'el-icon-circle-close',
                        style: {
                          color: '#fd9d9d',
                          margin: '5px 0px',
                          fontSize: '15px'
                        }
                      })
                  ]
                  )
                  ])
               ])
            }
        }
    };
</script>
