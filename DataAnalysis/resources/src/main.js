// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import ElementUI from 'element-ui'
import axios from 'axios'
import echarts from 'echarts'
import 'echarts/map/js/china.js'

import './assets/css/element-variables.scss'

import store from './store'

import '../static/utf8-php/ueditor.config.js'
import '../static/utf8-php/ueditor.all.min.js'
import '../static/utf8-php/lang/zh-cn/zh-cn.js'
import '../static/utf8-php/ueditor.parse.min.js'


Vue.use(ElementUI)
Vue.config.productionTip = false

axios.defaults.baseURL = '/index.php'
// Add a request interceptor
axios.interceptors.request.use(function (config) {
    // Do something before request is sent
    return config
}, function (error) {
    // Do something with request error
    return Promise.reject(error)
})

// Add a response interceptor
axios.interceptors.response.use(function (response) {
    // Do something with response data
    if (response.status === 203) {
      store.commit('pagePassValue/func_setRelogin', true)
    }

    if (response.status >= 500 && response.status <= 510) {
      if (document.getElementsByClassName('el-notification').length === 0) {
        Vue.prototype.$notify({title: '提示', message: '网络错误!', duration: 0, position: 'top-left'})
      }
    }
    if (response.data === 'bcbc64917f64d100313d1f15ccc191fb' ) {
        if (document.getElementsByClassName('el-notification').length === 0) {
            Vue.prototype.$notify({title: '提示', message: '你没有权限!', duration: 0, position: 'top-left'})
            this.$destroy()
        }
    }

    return response
}, function (error) {
    // Do something with response error
    return Promise.reject(error)
})


Vue.prototype.$ajax = axios
Vue.prototype.$echarts = echarts
Vue.prototype.$bus = new Vue()  // 空的实例放到根组件下，所有的子组件都能调用

export default new Vue({
  el: '#body',
  router,
  store,
  template: '<App />',
  components: { App },
  data: {
    Bus: new Vue()  // 空的实例放到根组件下，所有的子组件都能调用
  }
})
