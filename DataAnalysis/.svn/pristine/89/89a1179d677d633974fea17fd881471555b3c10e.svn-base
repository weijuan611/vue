// api请求 封装函数

import axios from 'axios'

export const apiPost = (url, obj) => {
  let response = {
    status: 200,
    data: {},
    message: ''
  }
  axios.post(url, obj).then(function(res) {
    console.log('res', res)
    data = res.data
  }).catch(function(err) {
    console.log('err', err)
  })
  return response
}

export const apiGet = (url) => {
  let response = {
    status: 200,
    data: {},
    message: ''
  }
  axios.get(url).then(function(res) {
    console.log('res', res)
    data = res.data
  }).catch(function(err) {
    console.log('err', err)
  })
  return response
}
