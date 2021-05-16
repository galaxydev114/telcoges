import * as types from './mutation-types'
import * as userTypes from '../user/mutation-types'
import * as rootTypes from '../../mutation-types'
import router from '@/router.js'

export const login = ({ commit }, data) => {
  return new Promise((resolve, reject) => {
    window.axios
      .post('/login', data)
      .then((response) => {
        if (response.data.success) {
          commit('user/' + userTypes.RESET_CURRENT_USER, null, { root: true })
          commit(rootTypes.UPDATE_APP_LOADING_STATUS, false, { root: true })
          resolve(response)
        } else {
          window.toastr['error'](response.data.message)
          reject(response)
        }
      })
      .catch((err) => {
        commit(types.AUTH_ERROR, err.response)
        reject(err)
      })
  })
}

export const register = ({ commit }, data) => {
  return new Promise((resolve, reject) => {
    window.axios
      .post('/register', data)
      .then((response) => {
        if (response.data.success) {
          commit('user/' + userTypes.RESET_CURRENT_USER, null, { root: true })
          commit(rootTypes.UPDATE_APP_LOADING_STATUS, false, { root: true })
          window.toastr['success'](response.data.message)
          resolve(response)
        } else {
          window.toastr['error'](response.data.message)
          reject(response)
        }
      })
      .catch((err) => {
        commit(types.AUTH_ERROR, err.response)
        reject(err)
      })
  })
}

export const setLogoutFalse = ({ commit }) => {
  commit(types.SET_LOGOUT, false)
}

export const logout = ({ state, commit }) => {
  return new Promise((resolve, reject) => {
    if (state.isLoggedOut) {
      resolve()
      return true
    }
    commit(types.SET_LOGOUT, true)

    window.axios
      .get('/auth/logout')
      .then(() => {
        router.push('/login')
        // window.toastr['success']('Logged out!', 'Success')
      })
      .catch((err) => {
        router.push('/login')
        reject(err)
      })
  })
}
