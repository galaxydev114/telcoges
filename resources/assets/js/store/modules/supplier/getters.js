export const suppliers = (state) => state.suppliers
export const selectAllField = (state) => state.selectAllField
export const selectedSuppliers = (state) => state.selectedSuppliers
export const totalSuppliers = (state) => state.totalSuppliers
export const getSupplier = (state) => (id) => {
  let CstId = parseInt(id)
  return state.suppliers.find((supplier) => supplier.id === CstId)
}
export const selectedViewSupplier = (state) => state.selectedViewSupplier
