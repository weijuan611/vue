<template>
    <el-dialog
      title="查找带回"
      :visible.sync="show"
      width="30%"
      :before-close="handleClose">

      <el-table
        ref="findback_table"
        :data="data"
        tooltip-effect="dark"
        style="width: 100%"
        @selection-change="handleSelectionChange">
        <el-table-column
          type="selection"
          width="80">
        </el-table-column>
        <el-table-column
          prop="id"
          label="ID">
        </el-table-column>
        <el-table-column
          prop="name"
          label="标签">
        </el-table-column>
      </el-table>

      <div slot="footer">
        <el-button size="mini" type="primary" plain @click="onClear">清空</el-button>
        <el-button size="mini" @click="onCancel">取 消</el-button>
        <el-button size="mini" type="primary" @click="onConfirm">确 定</el-button>
      </div>

    </el-dialog>
</template>

<script>
export default {
  props: {
    show: false
  },
  data () {
    return {
      data: [
        {
          id: 1,
          name: 'label1'
        },
        {
          id: 2,
          name: 'label2'
        },
        {
          id: 3,
          name: 'label3'
        }
      ]
    }
  },
  methods: {
    handleSelectionChange (val) {
      let ids = val.map(i => i.id)
    },
    handleClose () {
      this.show = false
    },
    onClear () {

    },
    onCancel () {

    },
    onConfirm () {

    }
  }
}
</script>
