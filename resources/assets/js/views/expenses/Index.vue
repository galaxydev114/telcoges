<template>
  <base-page>
    <!-- Page Header -->
    <sw-page-header :title="$t('expenses.title')">
      <sw-breadcrumb slot="breadcrumbs">
        <sw-breadcrumb-item :title="$t('general.home')" to="dashboard" />

        <sw-breadcrumb-item :title="$tc('expenses.expense', 2)" to="#" active />
      </sw-breadcrumb>

      <template slot="actions">
        <sw-button
          v-show="totalExpenses"
          size="lg"
          variant="primary-outline"
          @click="toggleFilter"
        >
          {{ $t('general.filter') }}
          <component :is="filterIcon" class="w-4 h-4 ml-2 -mr-1" />
        </sw-button>

        <sw-button
          tag-name="router-link"
          to="expenses/create"
          class="ml-4"
          size="lg"
          variant="primary"
        >
          <plus-icon class="w-6 h-6 mr-1 -ml-2" />
          {{ $t('expenses.add_expense') }}
        </sw-button>
      </template>
    </sw-page-header>

    <!--Filter Wrapper -->
    <slide-y-up-transition>
      <sw-filter-wrapper v-show="showFilters" class="mt-3">
        <sw-input-group :label="$t('expenses.supplier')" class="flex-1 mt-3">
          <base-supplier-select
            ref="supplierSelect"
            @select="onSelectSupplier"
            @deselect="clearSupplierSearch"
          />
        </sw-input-group>

        <sw-input-group
          :label="$t('expenses.category')"
          class="flex-1 mt-2 ml-0 lg:ml-6"
        >
          <sw-select
            v-model="filters.category"
            :options="categories"
            :searchable="true"
            :show-labels="false"
            :placeholder="$t('expenses.categories.select_a_category')"
            label="name"
            class="mt-2"
            @click="filter = !filter"
          />
        </sw-input-group>

        <sw-input-group
          :label="$t('expenses.from_date')"
          class="flex-1 mt-2 ml-0 lg:ml-6"
        >
          <base-date-picker
            v-model="filters.from_date"
            :calendar-button="true"
            class="mt-2"
            calendar-button-icon="calendar"
          />
        </sw-input-group>

        <sw-input-group
          :label="$t('expenses.to_date')"
          class="flex-1 mt-2 ml-0 lg:ml-6"
        >
          <base-date-picker
            v-model="filters.to_date"
            :calendar-button="true"
            class="mt-2"
            calendar-button-icon="calendar"
          />
        </sw-input-group>

        <label
          class="absolute text-sm leading-snug text-black cursor-pointer"
          style="top: 10px; right: 15px"
          @click="clearFilter"
          >{{ $t('general.clear_all') }}</label
        >
      </sw-filter-wrapper>
    </slide-y-up-transition>

    <!-- Empty Table Placeholder -->
    <sw-empty-table-placeholder
      v-show="showEmptyScreen"
      :title="$t('expenses.no_expenses')"
      :description="$t('expenses.list_of_expenses')"
    >
      <observatory-icon class="mt-5 mb-4" />

      <sw-button
        slot="actions"
        tag-name="router-link"
        to="/admin/expenses/create"
        size="lg"
        variant="primary-outline"
      >
        <plus-icon class="w-6 h-6 mr-1 -ml-2" />
        {{ $t('expenses.add_new_expense') }}
      </sw-button>
    </sw-empty-table-placeholder>

    <div v-show="!showEmptyScreen" class="relative table-container">
      <div
        class="relative flex items-center justify-between h-10 mt-5 list-none border-b-2 border-gray-200 border-solid"
      >
        <p class="text-sm">
          {{ $t('general.showing') }}: <b>{{ expenses.length }}</b>

          {{ $t('general.of') }} <b>{{ totalExpenses }}</b>
        </p>

        <sw-transition type="fade">
          <sw-dropdown v-if="selectedExpenses.length">
            <span
              slot="activator"
              class="flex block text-sm font-medium cursor-pointer select-none text-primary-400"
            >
              {{ $t('general.actions') }}
              <chevron-down-icon class="h-5" />
            </span>

            <sw-dropdown-item @click="removeMultipleExpenses">
              <trash-icon class="h-5 mr-3 text-gray-600" />
              {{ $t('general.delete') }}
            </sw-dropdown-item>
          </sw-dropdown>
        </sw-transition>
      </div>

      <div class="absolute z-10 items-center pl-4 mt-2 select-none md:mt-12">
        <sw-checkbox
          v-model="selectAllFieldStatus"
          variant="primary"
          size="sm"
          class="hidden md:inline"
          @change="selectAllExpenses"
        />

        <sw-checkbox
          v-model="selectAllFieldStatus"
          :label="$t('general.select_all')"
          variant="primary"
          size="sm"
          class="md:hidden"
          @change="selectAllExpenses"
        />
      </div>

      <sw-table-component
        ref="table"
        :show-filter="false"
        :data="fetchData"
        :filter-no-results="$t('table.filter_no_results')"
      >
        <sw-table-column
          :sortable="false"
          :filterable="false"
          cell-class="no-click"
        >
          <div slot-scope="row" class="relative block">
            <sw-checkbox
              :id="row.id"
              v-model="selectField"
              :value="row.id"
              variant="primary"
              size="sm"
            />
          </div>
        </sw-table-column>

        <sw-table-column
          :sortable="true"
          :label="$t('expenses.date')"
          sort-as="expense_date"
          show="formattedExpenseDate"
        />

        <sw-table-column
          :sortable="true"
          :label="$tc('expenses.categories.category', 1)"
          sort-as="name"
          show="category.name"
        >
          <template slot-scope="row">
            <span>{{ $tc('expenses.categories.category', 1) }}</span>
            <router-link
              :to="{ path: `expenses/${row.id}/edit` }"
              class="font-medium text-primary-500"
            >
              {{ row.category.name }}
            </router-link>
          </template>
        </sw-table-column>

        <sw-table-column
          :sortable="true"
          :label="$t('expenses.supplier')"
          sort-as="user_name"
          show="user_name"
        >
          <template slot-scope="row">
            <span>{{ $t('expenses.supplier') }}</span>
            <span>
              {{ row.user_name ? row.user_name : $t('expenses.not_selected') }}
            </span>
          </template>
        </sw-table-column>

        <sw-table-column :sortable="true" :label="$t('expenses.number')">
          <template slot-scope="row">
            <span>{{ $t('expenses.number') }}</span>
            <span>
              {{ row.doc_num }}
            </span>
          </template>
        </sw-table-column>

        <sw-table-column
          :sortable="true"
          :label="$t('invoices.paid_status')"
          sort-as="paid_status"
        >
          <template slot-scope="row">
            <span>{{ $t('invoices.paid_status') }}</span>

            <sw-badge
              :bg-color="$utils.getExpensePaidStatusColor(row.paid_status).bgColor"
              :color="$utils.getExpensePaidStatusColor(row.paid_status).color"
            >
              {{
                $utils.getLabelTranslation(row.paid_status.replace('_', ' '))
              }}
            </sw-badge>
          </template>
        </sw-table-column>

        <sw-table-column
          :sortable="true"
          :label="$t('expenses.amount')"
          sort-as="amount"
          show="category.amount"
        >
          <template slot-scope="row">
            <span>{{ $t('expenses.amount') }}</span>
            <div v-html="$utils.formatMoney(row.amount, defaultCurrency)" />
          </template>
        </sw-table-column>

        <sw-table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown no-click"
        >
          <template slot-scope="row">
            <span>{{ $t('expenses.action') }}</span>
            <sw-dropdown>
              <dot-icon slot="activator" />

              <sw-dropdown-item
                :to="`expenses/${row.id}/edit`"
                tag-name="router-link"
              >
                <pencil-icon class="h-5 mr-3 text-gray-600" />
                {{ $t('general.edit') }}
              </sw-dropdown-item>

              <sw-dropdown-item @click="removeExpense(row.id)">
                <trash-icon class="h-5 mr-3 text-gray-600" />
                {{ $t('general.delete') }}
              </sw-dropdown-item>

              <sw-dropdown-item
                v-if="row.paid_status == 'PENDING'"
                @click="markAsPaid(row.id)"
              >
                <check-circle-icon class="h-5 mr-3 text-gray-600" />
                {{ $t('sales.mark_as_paid') }}
              </sw-dropdown-item>

              <sw-dropdown-item
                v-if="row.paid_status == 'PAID'"
                @click="markAsPending(row.id)"
              >
                <stop-icon class="h-5 mr-3 text-gray-600" />
                {{ $t('sales.mark_as_pending') }}
              </sw-dropdown-item>
            </sw-dropdown>
          </template>
        </sw-table-column>
      </sw-table-component>
    </div>
  </base-page>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import ObservatoryIcon from '../../components/icon/ObservatoryIcon'
import moment, { invalid } from 'moment'
import {
  PencilIcon,
  TrashIcon,
  FilterIcon,
  XIcon,
  ChevronDownIcon,
  PlusIcon,
  CheckCircleIcon,
  StopIcon,
} from '@vue-hero-icons/solid'

export default {
  components: {
    ObservatoryIcon,
    PlusIcon,
    FilterIcon,
    XIcon,
    ChevronDownIcon,
    PencilIcon,
    TrashIcon,
    CheckCircleIcon,
    StopIcon,
  },

  data() {
    return {
      showFilters: false,
      isRequestOngoing: true,
      filters: {
        category: null,
        from_date: '',
        to_date: '',
        user: '',
      },
    }
  },

  computed: {
    showEmptyScreen() {
      return !this.totalExpenses && !this.isRequestOngoing
    },

    filterIcon() {
      return this.showFilters ? 'x-icon' : 'filter-icon'
    },

    ...mapGetters('category', ['categories']),

    ...mapGetters('expense', [
      'selectedExpenses',
      'totalExpenses',
      'expenses',
      'selectAllField',
    ]),

    ...mapGetters('company', ['defaultCurrency']),

    ...mapGetters('supplier', ['suppliers']),

    selectField: {
      get: function () {
        return this.selectedExpenses
      },
      set: function (val) {
        this.selectExpense(val)
      },
    },

    selectAllFieldStatus: {
      get: function () {
        return this.selectAllField
      },
      set: function (val) {
        this.setSelectAllState(val)
      },
    },
  },

  watch: {
    filters: {
      handler: 'setFilters',
      deep: true,
    },
  },

  destroyed() {
    if (this.selectAllField) {
      this.selectAllExpenses()
    }
  },

  created() {
    this.fetchCategories({ limit: 'all' })
  },

  methods: {
    ...mapActions('expense', [
      'fetchExpenses',
      'selectExpense',
      'deleteExpense',
      'deleteMultipleExpenses',
      'selectAllExpenses',
      'setSelectAllState',
      'updatePaidStatus',
    ]),

    ...mapActions('category', ['fetchCategories']),

    async fetchData({ page, filter, sort }) {
      let data = {
        user_id: this.filters.user ? this.filters.user.id : null,

        expense_category_id:
          this.filters.category !== null ? this.filters.category.id : '',

        from_date:
          this.filters.from_date === ''
            ? this.filters.from_date
            : this.filters.from_date,

        to_date:
          this.filters.to_date === ''
            ? this.filters.to_date
            : this.filters.to_date,

        orderByField: sort.fieldName || 'created_at',

        orderBy: sort.order || 'desc',

        page,
      }

      this.isRequestOngoing = true
      let response = await this.fetchExpenses(data)
      this.isRequestOngoing = false

      return {
        data: response.data.expenses.data,

        pagination: {
          totalPages: response.data.expenses.last_page,
          currentPage: page,
          count: response.data.expenses.count,
        },
      }
    },

    onSelectSupplier(supplier) {
      this.filters.user = supplier
    },

    refreshTable() {
      this.$refs.table.refresh()
    },

    setFilters() {
      this.refreshTable()
    },

    clearFilter() {
      if (this.filters.user) {
        this.$refs.supplierSelect.$refs.baseSelect.removeElement(
          this.filters.user
        )
      }

      this.filters = {
        category: null,
        from_date: '',
        to_date: '',
        user: null,
      }
    },

    async clearSupplierSearch(removedOption, id) {
      this.filters.user = ''
      this.refreshTable()
    },

    toggleFilter() {
      if (this.showFilters) {
        this.clearFilter()
      }

      this.showFilters = !this.showFilters
    },

    async removeExpense(id) {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('expenses.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true,
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteExpense({ ids: [id] })

          if (res.data.success) {
            window.toastr['success'](this.$tc('expenses.deleted_message', 1))
            this.refreshTable()
            return true
          } else if (res.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      })
    },

    async removeMultipleExpenses() {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('expenses.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true,
      }).then(async (willDelete) => {
        if (willDelete) {
          let request = await this.deleteMultipleExpenses()

          if (request.data.success) {
            window.toastr['success'](this.$tc('expenses.deleted_message', 2))
            this.$refs.table.refresh()
          } else if (request.data.error) {
            window.toastr['error'](request.data.message)
          }
        }
      })
    },

    async markAsPending(id) {
      const data = {
        id: id,
        paid_status: 'PENDING',
      }
      await this.updatePaidStatus(data)
      this.refreshTable()
    },

    async markAsPaid(id) {
      const data = {
        id: id,
        paid_status: 'PAID',
      }
      await this.updatePaidStatus(data)
      this.refreshTable()
    },
  },
}
</script>
