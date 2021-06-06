<template>
  <base-page class="xl:pl-96">
    <sw-page-header :title="pageTitle">
      <template slot="actions">
        <sw-button
          tag-name="router-link"
          :to="`/admin/contacts/suppliers/${$route.params.id}/edit`"
          class="mr-3"
          variant="primary-outline"
        >
          {{ $t('general.edit') }}
        </sw-button>
        <sw-dropdown position="bottom-end">
          <sw-button slot="activator" class="mr-3" variant="primary">
            {{ $t('suppliers.new_transaction') }}
          </sw-button>
          <sw-dropdown-item
            tag-name="router-link"
            :to="`/admin/estimates/create?supplier=${$route.params.id}`"
          >
            <document-icon class="h-5 mr-3 text-gray-600" />
            {{ $t('estimates.new_estimate') }}
          </sw-dropdown-item>
          <sw-dropdown-item
            tag-name="router-link"
            :to="`/admin/invoices/create?supplier=${$route.params.id}`"
          >
            <document-text-icon class="h-5 mr-3 text-gray-600" />
            {{ $t('invoices.new_invoice') }}
          </sw-dropdown-item>
          <sw-dropdown-item
            tag-name="router-link"
            :to="`/admin/payments/create?supplier=${$route.params.id}`"
          >
            <credit-card-icon class="h-5 mr-3 text-gray-600" />
            {{ $t('payments.new_payment') }}
          </sw-dropdown-item>
          <sw-dropdown-item
            tag-name="router-link"
            :to="`/admin/expenses/create?supplier=${$route.params.id}`"
          >
            <calculator-icon class="h-5 mr-3 text-gray-600" />
            {{ $t('expenses.new_expense') }}
          </sw-dropdown-item>
        </sw-dropdown>
        <sw-dropdown>
          <sw-button slot="activator" variant="primary">
            <dots-horizontal-icon class="h-5 -ml-1 -mr-1" />
          </sw-button>

          <sw-dropdown-item @click="removeSupplier($route.params.id)">
            <trash-icon class="h-5 mr-3 text-gray-600" />
            {{ $t('general.delete') }}
          </sw-dropdown-item>
        </sw-dropdown>
      </template>
    </sw-page-header>

    <!-- sidebar -->
    <supplier-view-sidebar />

    <!-- Chart -->
    <supplier-chart />
  </base-page>
</template>

<script>
import {
  DotsHorizontalIcon,
  TrashIcon,
  DocumentIcon,
  DocumentTextIcon,
  CreditCardIcon,
  CalculatorIcon,
} from '@vue-hero-icons/solid'
import LineChart from '../../components/chartjs/LineChart'
import SupplierViewSidebar from './partials/SupplierViewSidebar'
import SupplierChart from './partials/SupplierChart'
import { mapActions, mapGetters } from 'vuex'

export default {
  components: {
    LineChart,
    DotsHorizontalIcon,
    SupplierViewSidebar,
    DocumentIcon,
    DocumentTextIcon,
    CreditCardIcon,
    CalculatorIcon,
    SupplierChart,
    TrashIcon,
  },
  data() {
    return {
      supplier: null,
    }
  },
  computed: {
    ...mapGetters('supplier', ['selectedViewSupplier']),
    pageTitle() {
      return this.selectedViewSupplier.supplier
        ? this.selectedViewSupplier.supplier.name
        : ''
    },
  },
  created() {
    this.fetchViewSupplier({ id: this.$route.params.id })
  },
  methods: {
    ...mapActions('supplier', [
      'fetchViewSupplier',
      'selectSupplier',
      'deleteMultipleSuppliers',
    ]),

    async removeSupplier(id) {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('suppliers.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true,
      }).then(async (willDelete) => {
        if (willDelete) {
          let data = [id]
          this.selectSupplier(data)
          let res = await this.deleteMultipleSuppliers()
          if (res.data.success) {
            window.toastr['success'](this.$tc('suppliers.deleted_message'))
            this.$router.push('/admin/contacts/suppliers')
            return true
          } else if (request.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      })
    },
  },
}
</script>
