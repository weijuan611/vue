<template>
  <div class="tableWrap" :id="activeTabName">
    <slot>

    </slot>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex'

export default {
  computed: {
      ...mapState('navTabs', [
          'activeTabName', 'tabTableHeight'
      ])
  },
  mounted () {
    this.setTabTableHeight(this.calculateTabTableHeight())
    this.scrollPaneFunc()
    this.scrollTableFunc()
  },
  updated () {
    if (this.tabTableHeight !== this.calculateTabTableHeight()) {
      // console.log('this.tabTableHeight', this.tabTableHeight)
      // console.log('this.calculateTabTableHeight()', this.calculateTabTableHeight())
      this.setTabTableHeight(this.calculateTabTableHeight())
    }
    let that = this
    window.onresize = function () {
      that.setTabTableHeight(that.calculateTabTableHeight())
    }
  },
  methods: {
    ...mapMutations('navTabs', ['setTabTableHeight']),
    calculateTabTableHeight () {
      let currentTab = document.getElementById('pane-' + this.activeTabName)  // 当前标签页
      let contentHeight = document.getElementsByClassName('el-tabs__content')[0].offsetHeight  // 当前标签页内容的高度
      let topHeight = currentTab.getElementsByClassName('tableWrap').length > 0 ? currentTab.getElementsByClassName('tableWrap')[0].offsetTop : 0  // 当前标签页表格上面搜索栏的高度
      let widthTableHeaderHeight = currentTab.getElementsByClassName('withTableHeader').length > 0 ? currentTab.getElementsByClassName('withTableHeader')[0].offsetHeight : 0 // 当前标签页表格上面按钮栏的高度
      let bottomHeight = 40 // 当前标签页表格下面分页栏的高度
      let tabTableHeight = contentHeight - topHeight - bottomHeight - widthTableHeaderHeight
      return tabTableHeight
    },
    scrollTableFunc () {
      this.$bus.$on('triggerTable', (val) => {
        if (val) {
          let currentTab = document.getElementById('pane-' + this.activeTabName)
          let scrollTable = currentTab.querySelector('.el-table__body-wrapper')
          scrollTable.scrollTop = 0
        }
      })
    },
    scrollPaneFunc () {
      let that = this
      let scrollPane = document.getElementsByClassName('el-tabs__content')[0]
      scrollPane.onscroll = function () {
        let paneTop = scrollPane.scrollTop
        let currentPane = document.getElementById('pane-' + that.activeTabName)
        if (document.getElementById(that.activeTabName) !== null) {
          let table = currentPane.getElementsByClassName('tableWrap')[0]
          let currentTables = currentPane.getElementsByClassName('el-table')
          for (let i = 0; i < currentTables.length; i++) {
            if (currentTables[i].style.display !== 'none') {
              let tableHeader = currentTables[i].getElementsByClassName('el-table__header-wrapper')[0]
              if (table.offsetTop > 300 && paneTop > table.offsetTop - 60) {
                tableHeader.style.position = 'fixed'
                tableHeader.style.top = '66px'
                tableHeader.style.zIndex = 1
              } else {
                tableHeader.removeAttribute('style')
              }
            }
          }
        }
      }
    }
  }
}
</script>

<style>
  .el-table__body-wrapper {
    position: static!important;
  }
</style>
