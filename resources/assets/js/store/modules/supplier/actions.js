import * as types from './mutation-types'
import * as searchTypes from '../search/mutation-types'

export const fetchSuppliers = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios
      .get(`/api/v1/suppliers`, { params })
      .then((response) => {
        commit(types.BOOTSTRAP_SUPPLIERS, response.data.suppliers.data)
        commit(types.SET_TOTAL_SUPPLIERS, response.data.supplierTotalCount)
        commit(
          'search/' + searchTypes.SET_SUPPLIER_LISTS,
          response.data.suppliers.data,
          { root: true }
        )
        resolve(response)
      })
      .catch((err) => {
        reject(err)
      })
  })
}

export const fetchSupplier = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios
      .get(`/api/v1/suppliers/${params.id}`)
      .then((response) => {
        resolve(response)
      })
      .catch((err) => {
        reject(err)
      })
  })
}

export const fetchViewSupplier = ({ commit, dispatch }, params) => {
  return new Promise((resolve, reject) => {
    window.axios
      .get(`/api/v1/suppliers/${params.id}/stats`, { params })
      .then((response) => {
        commit(types.SET_SELECTED_VIEW_SUPPLIER, response.data)
        resolve(response)
      })
      .catch((err) => {
        reject(err)
      })
  })
}

export const addSupplier = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios
      .post('/api/v1/suppliers', data)
      .then((response) => {
        commit(types.ADD_SUPPLIER, response.data)
        resolve(response)
      })
      .catch((err) => {
        reject(err)
      })
  })
}

export const updateSupplier = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios
      .put(`/api/v1/suppliers/${data.id}`, data)
      .then((response) => {
        if (response.data.success) {
          commit(types.UPDATE_SUPPLIER, response.data)
        }
        resolve(response)
      })
      .catch((err) => {
        reject(err)
      })
  })
}

export const deleteSupplier = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios
      .post(`/api/v1/suppliers/delete`, id)
      .then((response) => {
        commit(types.DELETE_SUPPLIER, id)
        resolve(response)
      })
      .catch((err) => {
        reject(err)
      })
  })
}

export const deleteMultipleSupplier = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios
      .post(`/api/v1/suppliers/delete`, { ids: state.selectedSuppliers })
      .then((response) => {
        commit(types.DELETE_MULTIPLE_SUPPLIERS, state.selectedSuppliers)
        resolve(response)
      })
      .catch((err) => {
        reject(err)
      })
  })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllSuppliers = ({ commit, dispatch, state }) => {
  if (state.selectedSuppliers.length === state.suppliers.length) {
    commit(types.SET_SELECTED_SUPPLIERS, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allSupplierIds = state.suppliers.map((cust) => cust.id)
    commit(types.SET_SELECTED_SUPPLIERS, allSupplierIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const selectSupplier = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_SUPPLIERS, data)
  if (state.selectedSuppliers.length === state.suppliers.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}

export const resetSelectedSupplier = ({ commit, dispatch, state }, data) => {
  commit(types.RESET_SELECTED_SUPPLIER)
}