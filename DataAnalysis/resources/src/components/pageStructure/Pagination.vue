<template>
  <div class="paginationWrap">
    <el-pagination
            :layout="layout"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
            :current-page="paginationData.currentPage"
            :page-size="paginationData.pageSize"
            :total="paginationData.total">
    </el-pagination>
  </div>
</template>
<script>
    export default {
        props: {
            data:{
                type:Object,
                default:function () {
                    return {currentPage:1,pageSize:50,total:0}
                }
            },
            layout:{
                type:String,
                default:function () {
                    return "total, sizes, prev, pager, next, jumper"
                }
            }
        },
        watch:{
          data:function (data,old) {
              this.paginationData=data
          }
        },
        data() {
            return {
                paginationData:this.data
            }
        },
        methods: {
            handleSizeChange (val) {
                this.paginationData.currentPage = 1
                this.paginationData.pageSize = val
                this.$emit('submit', this.paginationData)
            },
            handleCurrentChange (val) {
                this.paginationData.currentPage = val
                this.$emit('submit', this.paginationData)
            }
        }
    }
</script>
