import * as types from './mutation-types'

export default {
  [types.BOOTSTRAP_SUPPLIERS](state, suppliers) {
    state.suppliers = suppliers
  },

  [types.SET_TOTAL_SUPPLIERS](state, totalSuppliers) {
    state.totalSuppliers = totalSuppliers
  },

  [types.ADD_SUPPLIER](state, data) {
    state.suppliers.push(data.supplier)
  },

  [types.UPDATE_SUPPLIER](state, data) {
    let pos = state.suppliers.findIndex(
      (supplier) => supplier.id === data.supplier.id
    )

    state.suppliers[pos] = data.supplier
  },

  [types.DELETE_SUPPLIER](state, id) {
    let index = state.suppliers.findIndex((supplier) => supplier.id === id)
    state.suppliers.splice(index, 1)
  },

  [types.DELETE_MULTIPLE_SUPPLIERS](state, selectedSuppliers) {
    selectedSuppliers.forEach((supplier) => {
      let index = state.suppliers.findIndex((_cust) => _cust.id === supplier.id)
      state.suppliers.splice(index, 1)
    })

    state.selectedSuppliers = []
  },

  [types.SET_SELECTED_SUPPLIERS](state, data) {
    state.selectedSuppliers = data
  },

  [types.RESET_SELECTED_SUPPLIER](state, data) {
    state.selectedViewSupplier = null
  },

  [types.SET_SELECT_ALL_STATE](state, data) {
    state.selectAllField = data
  },

  [types.SET_SELECTED_VIEW_SUPPLIER](state, selectedViewSupplier) {
    state.selectedViewSupplier = selectedViewSupplier
  },
}
