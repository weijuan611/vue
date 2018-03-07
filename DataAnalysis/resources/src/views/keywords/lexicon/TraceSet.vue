<template>
  <div>
    <!-- dialog追踪设置开始 -->
    <el-dialog
      width="400px"
      title="追踪设置"
      @close="handleTraceClose"
      :visible.sync="openTraceDialog">

      <!-- <el-dialog
        width="250px"
        title="添加/编辑标签"
        :visible.sync="openEditTagConfiguration"
        @close="handleEditTagCancel"
        append-to-body>
        <el-input v-model="tagsOperateData.tagName" size="mini"></el-input>
        <div slot="footer">
          <el-button size="mini" @click="handleEditTagCancel">取 消</el-button>
          <el-button size="mini" type="primary" @click="handleEditTagConfirm">确 定</el-button>
        </div>
      </el-dialog> -->

      <el-form ref="tag-form" :model="tagsOperateData" size="mini">
        <el-form-item>
          <el-input v-model="tagsOperateData.searchedTag" prefix-icon="el-icon-search" placeholder="请输入需要查询的标签" style="width:200px;margin-right: 30px;"></el-input>
          <el-button type="warning" plain @click="handleSearchTag">查询</el-button>
          <el-button type="success" plain @click="handleAddTag">添加</el-button>
        </el-form-item>
      </el-form>

      <el-table
        :data="tagsData"
        size="mini"
        :height="170">
        <el-table-column prop="name" label="标签"></el-table-column>
        <el-table-column label="操作">
          <template slot-scope="props">
            <el-button type="text" plain size="mini" icon="el-icon-edit" @click="handleEditTag($event, props.row.id, props.row.name)">编辑</el-button>
            <el-button type="text" plain size="mini" icon="el-icon-delete" @click="handleDeleteTagConfirm($event, props.row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div slot="footer">
        <el-pagination
          small
          layout="prev, pager, next"
          @current-change="changeCurrent"
          :pageSize="paginationData.pageSize"
          :total="tagsPaginationData.total">
        </el-pagination>
      </div>

    </el-dialog>
    <!-- dialog追踪设置结束 -->
  </div>
</template>

<script>
  export default {
    props: {
      show: {
        type: Boolean,
        default: false
      }
    },
    data () {
      return {
        openTraceDialog: false
      }
    },
    watch: {
      show() {
        this.openTraceDialog = this.show
      }
    },
    methods: {
      handleTraceClose () {
        this.$emit('close')
      }
    }
  }
</script>
