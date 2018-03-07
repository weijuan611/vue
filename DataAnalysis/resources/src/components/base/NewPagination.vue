<template>
  <el-pagination
    layout="total, sizes, prev, pager, next, jumper"
    @size-change="handleSizeChange"
    @current-change="handleCurrentChange"
    :current-page="paginationData.currentPage"
    :page-sizes="paginationData.pageSizes"
    :page-size="paginationData.pageSize"
    :total="paginationData.total">
  </el-pagination>
</template>

<script>
export default {
  props: {
    total: {
      type: Number,
      default: 0
    },
    propPageSize: {
      type: Number,
      default: 50
    },
    propPageSizes: {
      type: Array,
      default: function () {
        return [1, 20, 50, 100, 150, 200]
      }
    },
    propCurrentPage: {
      type: Number,
      default: 1
    }
  },
  data () {
    return {
      paginationData: {
        pageSizes: this.propPageSizes,
        pageSize: this.propPageSize,
        currentPage: this.propCurrentPage,
        total: this.total,
        order: 'descending',
        prop: 'id' // 默认以id排序
      }
    }
  },
  created () {
    this.initPaginationData()
  },
  watch: {
    total () {
      this.paginationData.total = this.total
    },
    propPageSize () {
      this.handleSizeChange(this.propPageSize)
    },
    propCurrentPage () {
      this.paginationData.currentPage = this.propCurrentPage
    }
  },
  methods: {
    initPaginationData () {
      this.$emit('initPaginationData', this.paginationData)
    },
    handleSizeChange (val) {
      this.handleCurrentChange(1)
      this.paginationData.pageSize = val
      this.$emit('handleSizeChange', val)
    },
    handleCurrentChange (val) {
      this.paginationData.currentPage = val
      this.$emit('handleCurrentChange', val)
      console.log('this.$bus', this.$bus)
      this.$bus.$emit('triggerTable', true)
    }
  }
}
</script>
