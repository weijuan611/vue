import Vue from 'vue'
import Router from 'vue-router'
import Login from './../views/Login'
import NullFind from './../views/404'
import Index from './../components/frame/Index'

import Home from './../views/Home'
// import Form from './../views/Form'
// import VisitDetail from './../views/flowAnalysis/VisitDetail'

Vue.use(Router)
const nullCom = Vue.component('root', {
  template: '<router-view></router-view>'
})

export default new Router({
  mode: 'history',
  routes: [
    {
      path: '/',
      component: Login
    },
    {
      path: '/login.html',
      name: 'login.html',
      component: Login
    },
    {
      path: '/404.html',
      name: '404.html',
      component: NullFind
    },
    {
      path: '/index.html',
      name: 'index.html',
      component: Index
    },
    {
      path: '*',
      redirect: { path: '/404.html' }
    }
  ]
})
