<template>
  <div class="sidebar">
    <el-menu
      :default-active="activeIndex"
      class="el-menu-vertical-demo"
      unique-opened >
      <template v-for="item in items">
        <template v-if="item.subs">
          <el-submenu :index="item.name">
            <template slot="title"><i :class="item.icon" :style="{color: item.iconColor}"></i>{{ item.title }}</template>
            <el-menu-item v-for="(subItem, i) in item.subs" :key="i" :title="subItem.name + '.html'" :index="subItem.name" @click="addTab({name:subItem.name, title:subItem.title})">
              {{ subItem.title }}
            </el-menu-item>
          </el-submenu>
        </template>
        <template v-else>
          <el-menu-item :index="item.name" :title="item.name + '.html'" @click="addTab({name:item.name, title:item.title})">
            <i :class="item.icon" :style="{color: item.iconColor}"></i>{{ item.title }}
          </el-menu-item>
        </template>
      </template>
    </el-menu>

    <el-dialog title="重新登录" :visible.sync="relogin" width="30%" size="mini" center>
      <el-form size="small" :model="ruleForm" :rules="rules" ref="ruleForm">
        <el-form-item prop="username" label="用户名：" label-width="100px">
          <el-input v-model="ruleForm.username" class="dialog-input"></el-input>
        </el-form-item>
        <el-form-item prop="password" label="密码：" label-width="100px">
          <el-input type="password" v-model="ruleForm.password"
                    @keyup.enter.native="submitForm('ruleForm')" class="dialog-input"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="relogin = false">取 消</el-button>
        <el-button type="primary" @click="submitForm('ruleForm')">确 定</el-button>
      </div>
    </el-dialog>

  </div>
</template>

<script>
  import $ from './../../assets/js/api/index'
  import { mapState, mapMutations } from 'vuex'


  export default {
    data () {
      return {
        activeIndex: 'home',
        items: [],
        ruleForm: {
            username: '',
            password: ''
        },
        rules: {
            username: [
                {required: true, message: '请输入用户名', trigger: 'blur'},
                {min:5,message:'用户名必须大于5位',trigger:'blur,change'}
            ],
            password: [
                {required: true, message: '请输入密码', trigger: 'blur'},
                {min:6, message: '密码必须大于6位',trigger: 'blur,change'}
            ]
        }
      }
    },
    computed: {
      ...mapState('navTabs', [
        'activeTabName'
      ]),
      relogin: {
          get() {
              return this.$store.state.pagePassValue.relogin;
          },
          set(value) {
              this.$store.commit("pagePassValue/func_setRelogin", value);
          }
      }
    },
    watch: {
      activeTabName () {
        this.activeIndex = this.activeTabName
      }
    },
    methods: {
      ...mapMutations('navTabs', [
        'addTab'
      ]),
      submitForm (formName) {
          const self = this
          self.$refs[formName].validate((valid) => {
              if (valid) {
                  self.$ajax.post('/login',self.ruleForm)
                      .then(function(res){
                          if(res.data !== false){
                              localStorage.setItem('ms_username',res.data);
                              location.replace('/index.html');
                              this.relogin = false
                          }else{
                              self.$message.error('账号或密码错误！')
                          }
                      })
                      .catch(function(err){
                          self.$message('网络错误！请联系管理员')
                      });
              } else {
                  return false
              }
          })
      },
      initMenu () {
        let menus = [
          {
            name: 'Home',
            title: '工作台',
            icon: 'el-icon-menu',
            iconColor: '#20a0ff'
          }
        ]
        let color = ['#FF4090', '#f9ae2a', '#9069f8', '#f2f26a', '#41e99c', '#79c6ff']
        let that = this
         this.$ajax.get('/menu')
           .then(function(res) {
             if (res.data.length > 0) {
               res.data.forEach((item, index) => {
                 menus.push({
                   title: item.title,
                   name: item.title,
                   icon: item.icon,
                   iconColor: color[index],
                   subs:item.subs
                 })

                // let subMenu = []
                // if (item.subs.length > 0) {
                //   item.subs.forEach((subitem, i) => {
                //     subMenu.push({
                //       title: subitem.title,
                //       name: subitem.name
                //     })
                //   })
                // }
                // menus[index + 1].subs = subMenu

               })
             }
           })
           .catch(function(err) {
           })
        return menus
      }
    },
    created () {
      this.items = this.initMenu()
      // console.log('this.items', this.items)
    }
  }
</script>
