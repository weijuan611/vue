<template>
  <el-dialog
    width="600px"
    title="追踪设置"
    @close="handleTraceClose"
    :visible.sync="openTraceDialog">

    <el-dialog
      width="400px"
      title="添加/编辑追踪"
      :visible.sync="openEdit"
      @close="handleEditCancel"
      append-to-body>
      <el-form label-width="100px">
        <el-form-item prop="op_name" label="追踪名称：" required>
          <el-input v-model="newTrace.op_name" size="mini"></el-input>
        </el-form-item>
        <el-form-item prop="op_domain" label="追踪url：" required>
          <el-input v-model="newTrace.op_domain" size="mini"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer">
        <el-button size="mini" @click="handleEditCancel">取 消</el-button>
        <el-button size="mini" type="primary" @click="handleEditConfirm">确 定</el-button>
      </div>
    </el-dialog>

    <el-button type="success" size="mini" plain @click="handleAdd" style="margin-top:-30px;margin-bottom: 10px;">添加</el-button>

    <el-table
      :data="tableData"
      size="mini">
      <el-table-column prop="op_name" label="追踪名称"></el-table-column>
      <el-table-column prop="op_domain" label="URL"></el-table-column>
      <el-table-column label="操作">
        <template slot-scope="props">
          <el-button type="text" plain size="mini" icon="el-icon-edit" @click="handleEdit($event, props.row.op_id, props.row.op_name, props.row.op_domain)">编辑</el-button>
          <el-button type="text" plain size="mini" icon="el-icon-delete" @click="handleDelete($event, props.row.op_id)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

  </el-dialog>
</template>

<script>
  import ElForm from "../../../../node_modules/element-ui/packages/form/src/form.vue";

  export default {
      components: {ElForm},
      props: {
      show: {
        type: Boolean,
        default: false
      },
      click: {
          type: Boolean,
          default: false
      }
    },
    data () {
      return {
        openTraceDialog: false,
        openEdit: false,
        tableData: [
          {
            op_id: 1,
            op_name: '',
            op_domain: ''
          },
          {
            op_id: 2,
            op_name: '',
            op_domain: ''
          },
          {
            op_id: 3,
            op_name: '',
            op_domain: ''
          },
        ],
        newTrace: {
          op_id: 0,
          op_name: '',
          op_domain: ''
        }
      }
    },
    mounted () {
//        this.initClassList()
    },
    watch: {
      show () {
        this.openTraceDialog = this.show
      },
      click() {
          this.initClassList()
      }
    },
    methods: {
      handleAdd () {
        this.openEdit = true
      },
      initClassList () {
            let that = this
            this.$ajax.get('/lexicon/opponent/list')
                .then(function (res) {
                    if (res.data !== undefined) {
                        console.log('res.data.initTraceset', res.data.initTraceSet)
                        that.tableData = res.data.initTraceSet
//                        that.filterTraceCompare = JSON.parse(JSON.stringify(that.initTraceCompare))
//                        that.copyFilterTraceCompare = JSON.parse(JSON.stringify(that.initTraceCompare))
//                        that.copyFilterTraceCompareArray = that.switchObjArrayToArray(that.copyFilterTraceCompare)
                    }
                })
                .catch(function (err) {
                })
        },
      handleEdit (e, id, name, url) {
        this.openEdit = true
        this.newTrace.op_id = id
        this.newTrace.op_name = name
        this.newTrace.op_domain = url
      },
      handleDelete (e, id) {
        this.$confirm('确认删除吗?', '提示', {
         confirmButtonText: '确定',
         cancelButtonText: '取消',
         type: 'warning'
       }).then(() => {
          let that = this
          this.$ajax.post('/lexicon/opponent/delete', {id: id})
              .then(function (res) {
                  if (res.data.type) {
                      that.openEdit = false
                      that.initClassList()
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
           message: '已取消删除'
         });
       });
      },
      handleEditCancel () {
        this.newTrace.op_id = 0
        this.newTrace.op_name = ''
        this.newTrace.op_domain = ''
        this.openEdit = false
      },
      handleEditConfirm () {
        let obj = {
          id: this.newTrace.op_id,
          name: this.newTrace.op_name,
          url: this.newTrace.op_domain
        }
        if (obj.id === 0) {
            obj.type = 'add'
        } else {
            obj.type = 'edit'
        }
        // this.newTrace.id = 0
        // this.newTrace.name = ''
        this.openEdit = false
        let that = this
        this.$ajax.post('/lexicon/opponent/'+obj.type, {id:obj.id, name: obj.name, url:obj.url})
            .then(function (res) {
                if (res.data.type) {
                    that.openEdit = false
                    that.initClassList()
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
      },
      handleTraceClose () {
        this.$emit('close')
      }
    }
  }
</script>
