<template>
    <div class="content">
        <el-tabs class="tabs" v-model="activeTabName" @tab-remove="closeTab" type="border-card" @mouseover.native="mouseoverFunc($event)">
            <el-tab-pane v-for="item in tabList" :key="item.name" :name="item.name" :label="item.label" :closable="item.closable">
                <component :is="item.component" :freshTab="freshTabName"></component>
            </el-tab-pane>
        </el-tabs>
        <a class="urlList">
          <el-dropdown trigger="click" @command="setActiveTabName">
            <span class="el-dropdown-link"><i class="el-icon-more"></i></span>
            <el-dropdown-menu slot="dropdown">
              <template v-for="(item, index) in tabList">
                <el-dropdown-item :command="item.name">
                  {{item.label}}
                </el-dropdown-item>
              </template>
            </el-dropdown-menu>
          </el-dropdown>
        </a>
        <ul id="rightMenu">
          <!-- <li @click="freshSelfTabFunc(activeTab)"><i class="el-icon-refresh"> 刷新本标签</i></li> -->
          <li @click="closeSelfTabFunc(activeTab)"><i class="el-icon-close"> 关闭本标签</i></li>
          <li @click="closeOtherTabFunc(activeTab)"><i class="el-icon-close"> 关闭其它标签</i></li>
          <li @click="closeAllTabFunc(activeTab)"><i class="el-icon-close"> 关闭所有标签</i></li>
        </ul>
    </div>
</template>
<script>
import { mapState, mapMutations } from 'vuex'
import Vue from 'vue'

export default {
    name: 'RightPane',
    data () {
      return {
        activeTab: 'Home'
      }
    },
    computed: {
        activeTabName: {
            get() {
                return this.$store.state.navTabs.activeTabName;
            },
            set(value) {
                this.$store.commit("navTabs/setActiveTabName", value);
            }
        },
        ...mapState('navTabs',[
            'tabList', 'freshTabName'
        ])
    },
    mounted () {
      window.onload = function () {
        let rightMenu = document.getElementById('rightMenu')
        document.onclick = function () {
          rightMenu.style.display = 'none'
        }
      }
    },
    methods: {
        ...mapMutations('navTabs',[
            'closeTab'
        ]),
        ...mapMutations('navTabs', [   // 下拉框选择所有已打开的标签
            'setActiveTabName'
        ]),
        ...mapMutations('navTabs', [   // 下拉框选择所有已打开的标签
            'closeSelfTabFunc'
        ]),
        ...mapMutations('navTabs', [   // 下拉框选择所有已打开的标签
            'closeOtherTabFunc'
        ]),
        ...mapMutations('navTabs', [   // 下拉框选择所有已打开的标签
            'closeAllTabFunc'
        ]),
        mouseoverFunc: function (e) {
          if (e.target.className.indexOf('el-tabs__item') > -1) {
            this.activeTab = e.target.id.substring(4)
            let currentTab = {
              name: e.target.id.substring(4, e.target.id.length),
              title: e.target.innerText
            }
            let rightMenu = document.getElementById('rightMenu')
            this.closedTab = currentTab
            e.target.oncontextmenu = function (event) {
              let e = event || window.event
              e.preventDefault()
              rightMenu.style.display = 'block'
              rightMenu.style.left = (e.pageX - 210) + 'px'
            }
          }
        }
    }
}
</script>
