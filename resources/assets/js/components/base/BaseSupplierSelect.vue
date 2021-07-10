<template>
  <div class="item-selector">
    <sw-select
      ref="baseSelect"
      v-model="supplierSelect"
      :options="suppliers"
      :show-labels="false"
      :preserve-search="false"
      :placeholder="$t('suppliers.type_or_click')"
      label="name"
      class="multi-select-item"
      @close="checkSuppliers"
      @value="onTextChange"
      @select="(val) => $emit('select', val)"
      @remove="deselectSupplier"
    />
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
  data() {
    return {
      supplierSelect: null,
      loading: false,
    }
  },
  computed: {
    ...mapGetters('supplier', ['suppliers']),
  },

  created() {
    this.fetchSuppliers()
  },

  methods: {
    ...mapActions('supplier', ['fetchSuppliers']),
    async searchSuppliers(search) {
      this.loading = true

      await this.fetchSuppliers({ search })

      this.loading = false
    },
    onTextChange(val) {
      this.searchSuppliers(val)
    },
    checkSuppliers(val) {
      if (!this.suppliers.length) {
        this.fetchSuppliers()
      }
    },
    deselectSupplier() {
      this.supplierSelect = null
      this.$emit('deselect')
    },
  },
}
</script>
