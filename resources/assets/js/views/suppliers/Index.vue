<template>
  <base-page class="suppler-index">
    <sw-page-header :title="$t('suppliers.title')">
      <sw-breadcrumb slot="breadcrumbs">
        <sw-breadcrumb-item :title="$t('general.home')" to="dashboard" />
        <sw-breadcrumb-item
          :title="$tc('suppliers.supplier', 2)"
          to="#"
          active
        />
      </sw-breadcrumb>

      <template slot="actions">
        <sw-button
          v-show="totalSuppliers"
          size="lg"
          variant="primary-outline"
          @click="toggleFilter"
        >
          {{ $t('general.filter') }}
          <component :is="filterIcon" class="h-4 ml-1 -mr-1 font-bold" />
        </sw-button>

        <sw-button
          tag-name="router-link"
          to="suppliers/create"
          size="lg"
          variant="primary"
          class="ml-4"
        >
          <plus-sm-icon class="h-6 mr-1 -ml-2 font-bold" />
          {{ $t('suppliers.new_supplier') }}
        </sw-button>
      </template>
    </sw-page-header>

    <slide-y-up-transition>
      <sw-filter-wrapper v-show="showFilters">
        <sw-input-group
          :label="$t('suppliers.display_name')"
          class="flex-1 mt-2"
        >
          <sw-input
            v-model="filters.display_name"
            type="text"
            name="name"
            class="mt-2"
            autocomplete="off"
          />
        </sw-input-group>

        <!-- <sw-input-group
          :label="$t('suppliers.contact_name')"
          class="flex-1 mt-2 ml-0 lg:ml-6"
        >
          <sw-input
            v-model="filters.contact_name"
            type="text"
            name="address_name"
            class="mt-2"
            autocomplete="off"
          />
        </sw-input-group> -->

        <sw-input-group
          :label="$t('suppliers.phone')"
          class="flex-1 mt-2 ml-0 lg:ml-6"
        >
          <sw-input
            v-model="filters.phone"
            type="text"
            name="phone"
            class="mt-2"
            autocomplete="off"
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

    <sw-empty-table-placeholder
      v-show="showEmptyScreen"
      :title="$t('suppliers.no_suppliers')"
      :description="$t('suppliers.list_of_suppliers')"
    >
      <astronaut-icon class="mt-5 mb-4" />

      <sw-button
        slot="actions"
        tag-name="router-link"
        to="suppliers/create"
        size="lg"
        variant="primary-outline"
      >
        {{ $t('suppliers.add_new_supplier') }}
      </sw-button>
    </sw-empty-table-placeholder>

    <div v-show="!showEmptyScreen" class="relative table-container">
      <div
        class="relative flex items-center justify-between h-10 mt-5 border-b-2 border-gray-200 border-solid"
      >
        <p class="text-sm">
          {{ $t('general.showing') }}: <b>{{ suppliers.length }}</b>
          {{ $t('general.of') }} <b>{{ totalSuppliers }}</b>
        </p>

        <sw-transition type="fade">
          <sw-dropdown v-if="selectedSuppliers.length">
            <span
              slot="activator"
              class="flex block text-sm font-medium cursor-pointer select-none text-primary-400"
            >
              {{ $t('general.actions') }}
              <chevron-down-icon class="h-5" />
            </span>

            <sw-dropdown-item @click="removeMultipleSuppliers">
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
          @change="selectAllSuppliers"
        />

        <sw-checkbox
          v-model="selectAllFieldStatus"
          :label="$t('general.select_all')"
          variant="primary"
          size="sm"
          class="md:hidden"
          @change="selectAllSuppliers"
        />
      </div>

      <sw-table-component
        ref="table"
        :show-filter="false"
        :data="fetchData"
        table-class="table"
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
          :filterable="true"
          :label="$t('suppliers.display_name')"
          show="name"
        >
          <template slot-scope="row">
            <span>{{ $t('suppliers.display_name') }}</span>
            <router-link
              :to="{ path: `suppliers/${row.id}/view` }"
              class="font-medium text-primary-500"
            >
              {{ row.name }}
            </router-link>
          </template>
        </sw-table-column>

        <sw-table-column
          :sortable="true"
          :label="$t('suppliers.email')"
          show="email"
        >
          <template slot-scope="row">
            <span>{{ $t('suppliers.email') }}</span>
            <span>
              {{ row.email ? row.email : $t('suppliers.no_email') }}
            </span>
          </template>
        </sw-table-column>

        <sw-table-column
          :sortable="true"
          :label="$t('suppliers.phone')"
          show="phone"
        >
          <template slot-scope="row">
            <span>{{ $t('suppliers.phone') }}</span>
            <span>
              {{ row.phone ? row.phone : $t('suppliers.no_contact') }}
            </span>
          </template>
        </sw-table-column>

        <sw-table-column
          :sortable="true"
          :label="$t('suppliers.amount_due')"
          show="due_amount"
        >
          <template slot-scope="row">
            <span> {{ $t('suppliers.amount_due') }} </span>
            <div v-html="$utils.formatMoney(row.due_amount, row.currency)" />
          </template>
        </sw-table-column>

        <sw-table-column
          :sortable="true"
          :label="$t('suppliers.added_on')"
          sort-as="created_at"
          show="formattedCreatedAt"
        />

        <sw-table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown"
        >
          <template slot-scope="row">
            <span> {{ $t('suppliers.action') }} </span>

            <sw-dropdown>
              <dot-icon slot="activator" />

              <sw-dropdown-item
                :to="`suppliers/${row.id}/edit`"
                tag-name="router-link"
              >
                <pencil-icon class="h-5 mr-3 text-gray-600" />
                {{ $t('general.edit') }}
              </sw-dropdown-item>

              <sw-dropdown-item
                :to="`suppliers/${row.id}/view`"
                tag-name="router-link"
              >
                <eye-icon class="h-5 mr-3 text-gray-600" />
                {{ $t('general.view') }}
              </sw-dropdown-item>

              <sw-dropdown-item @click="removeSupplier(row.id)">
                <trash-icon class="h-5 mr-3 text-gray-600" />
                {{ $t('general.delete') }}
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
import { PlusSmIcon } from '@vue-hero-icons/solid'
import {
  FilterIcon,
  XIcon,
  ChevronDownIcon,
  TrashIcon,
  PencilIcon,
  EyeIcon,
} from '@vue-hero-icons/solid'
import AstronautIcon from '../../components/icon/AstronautIcon'

export default {
  components: {
    AstronautIcon,
    ChevronDownIcon,
    PlusSmIcon,
    FilterIcon,
    XIcon,
    TrashIcon,
    PencilIcon,
    EyeIcon,
  },
  data() {
    return {
      showFilters: false,
      isRequestOngoing: true,
      filters: {
        display_name: '',
        contact_name: '',
        phone: '',
      },
    }
  },
  computed: {
    showEmptyScreen() {
      return !this.totalSuppliers && !this.isRequestOngoing
    },
    filterIcon() {
      return this.showFilters ? 'x-icon' : 'filter-icon'
    },
    ...mapGetters('supplier', [
      'suppliers',
      'selectedSuppliers',
      'totalSuppliers',
      'selectAllField',
    ]),
    selectField: {
      get: function () {
        return this.selectedSuppliers
      },
      set: function (val) {
        this.selectSupplier(val)
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
      this.selectAllSuppliers()
    }
  },
  methods: {
    ...mapActions('supplier', [
      'fetchSuppliers',
      'selectAllSuppliers',
      'selectSupplier',
      'deleteSupplier',
      'deleteMultipleSuppliers',
      'setSelectAllState',
    ]),
    refreshTable() {
      this.$refs.table.refresh()
    },
    async fetchData({ page, filter, sort }) {
      let data = {
        display_name: this.filters.display_name,
        contact_name: this.filters.contact_name,
        phone: this.filters.phone,
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page,
      }

      this.isRequestOngoing = true
      let response = await this.fetchSuppliers(data)
      this.isRequestOngoing = false

      return {
        data: response.data.suppliers.data,
        pagination: {
          totalPages: response.data.suppliers.last_page,
          currentPage: page,
        },
      }
    },
    setFilters() {
      this.refreshTable()
    },
    clearFilter() {
      this.filters = {
        display_name: '',
        contact_name: '',
        phone: '',
      }
    },
    toggleFilter() {
      if (this.showFilters) {
        this.clearFilter()
      }

      this.showFilters = !this.showFilters
    },

    async removeSupplier(id) {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('suppliers.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true,
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteSupplier({ ids: [id] })

          if (res.data.success) {
            window.toastr['success'](this.$tc('suppliers.deleted_message', 1))
            this.$refs.table.refresh()
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },

    async removeMultipleSuppliers() {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('suppliers.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true,
      }).then(async (willDelete) => {
        if (willDelete) {
          let request = await this.deleteMultipleSuppliers()
          if (request.data.success) {
            window.toastr['success'](this.$tc('suppliers.deleted_message', 2))
            this.refreshTable()
          } else if (request.data.error) {
            window.toastr['error'](request.data.message)
          }
        }
      })
    },
  },
}
</script>
