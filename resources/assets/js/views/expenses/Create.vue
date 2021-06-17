<template>
  <base-page class="relative">
    <form v-if="!isRequestOnGoing" @submit.prevent="submitForm">
      <sw-page-header :title="pageTitle">
        <sw-breadcrumb slot="breadcrumbs">
          <sw-breadcrumb-item
            :title="$t('general.home')"
            to="/admin/dashboard"
          />
          <sw-breadcrumb-item
            :title="$tc('expenses.expense', 2)"
            to="/admin/expenses"
          />
          <sw-breadcrumb-item
            v-if="$route.name === 'expenses.edit'"
            :title="$t('expenses.edit_expense')"
            to="#"
            active
          />
          <sw-breadcrumb-item
            v-else
            :title="$t('expenses.new_expense')"
            to="#"
            active
          />
        </sw-breadcrumb>

        <template slot="actions">
          <sw-button
            v-if="isReceiptAvailable"
            :href="getReceiptUrl"
            tag-name="a"
            variant="primary"
            outline
            size="lg"
            class="mr-2"
          >
            <download-icon class="h-5 mr-2 -ml-1" />
            {{ $t('expenses.download_receipt') }}
          </sw-button>

          <div class="hidden md:block">
            <sw-button
              :loading="isLoading"
              :disabled="isLoading"
              variant="primary"
              type="submit"
              size="lg"
            >
              <save-icon v-if="!isLoading" class="mr-2 -ml-1" />
              {{
                isEdit
                  ? $t('expenses.update_expense')
                  : $t('expenses.save_expense')
              }}
            </sw-button>
          </div>
        </template>
      </sw-page-header>

      <!-- <base-loader v-if="isRequestOnGoing" :show-bg-overlay="true" /> -->

      <!-- <sw-card v-else> -->
      <sw-card>
        <div class="grid gap-6 grid-col-1 md:grid-cols-4">
          <sw-input-group
            :label="$t('expenses.supplier')"
            :error="supplierError"
            required
          >
            <sw-select
              ref="baseSelect"
              v-model="supplier"
              :options="suppliers"
              :invalid="$v.supplier.$error"
              :searchable="true"
              :show-labels="false"
              :placeholder="$t('suppliers.select_a_supplier')"
              class="mt-1"
              label="name"
              track-by="id"
            />
          </sw-input-group>

          <sw-input-group
            :label="$t('general.doc_num')"
            :error="docNumError"
            required
          >
            <sw-input
              v-model="formData.doc_num"
              :invalid="$v.formData.doc_num.$error"
              class="mt-2"
              @input="$v.formData.doc_num.$touch()"
            >
              <hashtag-icon slot="leftIcon" class="h-4 ml-1 text-gray-500" />
            </sw-input>
          </sw-input-group>

          <sw-input-group
            :label="$t('expenses.expense_date')"
            :error="invoiceDateError"
            required
          >
            <base-date-picker
              v-model="formData.expense_date"
              :calendar-button="true"
              calendar-button-icon="calendar"
              class="mt-2"
              @input="$v.formData.expense_date.$touch()"
            />
          </sw-input-group>

          <sw-input-group
            :label="$t('expenses.expense_due')"
            :error="dueDateError"
            required
          >
            <base-date-picker
              v-model="formData.expense_due"
              :invalid="$v.formData.expense_due.$error"
              :calendar-button="true"
              calendar-button-icon="calendar"
              class="mt-2"
              @input="$v.formData.expense_due.$touch()"
            />
          </sw-input-group>
        </div>
        <div class="grid gap-6 grid-col-1 md:grid-cols-1 mt-3">
          <sw-input-group
            :label="$t('expenses.category')"
            :error="categoryError"
            required
          >
            <sw-select
              ref="baseSelect"
              v-model="category"
              :options="categories"
              :invalid="$v.category.$error"
              :searchable="true"
              :show-labels="false"
              :placeholder="$t('expenses.categories.select_a_category')"
              class="mt-2"
              label="name"
              track-by="id"
              @input="$v.category.$touch()"
            >
              <sw-button
                slot="afterList"
                type="button"
                variant="gray-light"
                class="flex items-center justify-center w-full px-4 py-3 bg-gray-200 border-none outline-none"
                @click="openCategoryModal"
              >
                <plus-sm-icon class="h-5 text-center text-primary-400" />
                <label class="ml-2 text-xs leading-none text-primary-400">{{
                  $t('settings.expense_category.add_new_category')
                }}</label>
              </sw-button>
            </sw-select>
          </sw-input-group>
        </div>
      </sw-card>

      <div class="grid-cols-1 gap-8 mt-6 mb-8 lg:grid">
        <sw-input-group v-if="!previewReceipt" :label="$t('expenses.receipt')">
          <div
            id="receipt-box"
            class="relative flex items-center justify-center h-24 p-6 bg-transparent border-2 border-gray-200 border-dashed rounded-md image-upload-box"
          >
            <div class="flex flex-col items-center">
              <cloud-upload-icon
                class="h-5 mb-2 text-xl leading-6 text-gray-400"
              />
              <p class="text-xs leading-4 text-center text-gray-400">
                Arrastre un archivo aquí o
                <span id="pick-avatar" class="cursor-pointer text-primary-500"
                  >navegar</span
                >
                elegir un archivo
              </p>
            </div>
          </div>
          <sw-avatar
            :preview-avatar="previewReceipt"
            :enable-cropper="false"
            trigger="#receipt-box"
            @changed="onChange"
          >
            <template v-slot:icon>
              <cloud-upload-icon
                class="h-5 mb-2 text-xl leading-6 text-gray-400"
              />
            </template>
          </sw-avatar>
        </sw-input-group>
        <div v-else class="pt-5">
          <div class="hero-text-btn d-flex float-right" @click="onRemoveFile">
            <trash-icon class="h-5 mr-3 text-gray-600" />
            {{ $t('general.remove_file') }}
          </div>
          <div
            class="flex flex-col min-h-0 mt-8 overflow-hidden"
            style="height: 25vh"
          >
            <iframe
              :src="previewReceipt"
              class="flex-1 border border-gray-400 border-solid rounded-md frame-style"
            />
          </div>
        </div>
      </div>

      <!-- Items -->
      <table class="w-full text-center item-table">
        <colgroup>
          <col style="width: 40%" />
          <col style="width: 10%" />
          <col style="width: 15%" />
          <col v-if="discountPerItem === 'YES'" style="width: 15%" />
          <col style="width: 15%" />
        </colgroup>
        <thead class="bg-white border border-gray-200 border-solid">
          <tr>
            <th
              class="px-5 py-3 text-sm not-italic font-medium leading-5 text-left text-gray-700 border-t border-b border-gray-200 border-solid"
            >
              <span class="pl-12">
                {{ $tc('items.item', 2) }}
              </span>
            </th>
            <th
              class="px-5 py-3 text-sm not-italic font-medium leading-5 text-right text-gray-700 border-t border-b border-gray-200 border-solid"
            >
              {{ $t('invoices.item.quantity') }}
            </th>
            <th
              class="px-5 py-3 text-sm not-italic font-medium leading-5 text-left text-gray-700 border-t border-b border-gray-200 border-solid"
            >
              {{ $t('invoices.item.price') }}
            </th>
            <th
              v-if="discountPerItem === 'YES'"
              class="px-5 py-3 text-sm not-italic font-medium leading-5 text-left text-gray-700 border-t border-b border-gray-200 border-solid"
            >
              {{ $t('invoices.item.discount') }}
            </th>
            <th
              class="px-5 py-3 text-sm not-italic font-medium leading-5 text-right text-gray-700 border-t border-b border-gray-200 border-solid"
            >
              <span class="pr-10">
                {{ $t('invoices.item.amount') }}
              </span>
            </th>
          </tr>
        </thead>

        <draggable
          v-model="formData.items"
          class="item-body"
          tag="tbody"
          handle=".handle"
        >
          <invoice-item
            v-for="(item, index) in formData.items"
            :key="item.id"
            :index="index"
            :item-data="item"
            :invoice-items="formData.items"
            :currency="currency"
            :tax-per-item="taxPerItem"
            :discount-per-item="discountPerItem"
            @remove="removeItem"
            @update="updateItem"
            @itemValidate="checkItemsData"
          />
        </draggable>
      </table>

      <div
        class="flex items-center justify-center w-full px-6 py-3 text-base border-b border-gray-200 border-solid cursor-pointer text-primary-400 hover:bg-gray-200"
        @click="addItem"
      >
        <shopping-cart-icon class="h-5 mr-2" />
        {{ $t('invoices.add_item') }}
      </div>

      <!-- Notes, Custom Fields & Total Section -->
      <div
        class="block my-10 invoice-foot lg:justify-between lg:flex lg:items-start"
      >
        <div class="w-full lg:w-1/2">
          <div class="mb-6">
            <sw-input-group :label="$t('expenses.note')" :error="notesError">
              <sw-textarea
                v-model="formData.notes"
                rows="4"
                @input="$v.formData.notes.$touch()"
              />
            </sw-input-group>
          </div>

          <div v-if="customFields.length > 0">
            <div class="grid gap-6 mt-6 grid-col-1 md:grid-cols-2">
              <sw-input-group
                v-for="(field, index) in customFields"
                :label="field.label"
                :required="field.is_required ? true : false"
                :key="index"
              >
                <component
                  :type="field.type.label"
                  :field="field"
                  :is-edit="isEdit"
                  :is="field.type + 'Field'"
                  :invalid-fields="invalidFields"
                  @update="setCustomFieldValue"
                />
              </sw-input-group>
            </div>
          </div>
        </div>

        <div
          class="px-5 py-4 mt-6 bg-white border border-gray-200 border-solid rounded invoice-total lg:mt-0 w-full lg:w-1/4"
        >
          <div class="flex items-center justify-between w-full">
            <label
              class="text-sm font-semibold leading-5 text-gray-500 uppercase"
              >{{ $t('invoices.sub_total') }}</label
            >
            <label
              class="flex items-center justify-center m-0 text-lg text-black uppercase"
            >
              <div v-html="$utils.formatMoney(subtotal, currency)" />
            </label>
          </div>
          <div
            v-for="tax in allTaxes"
            :key="tax.tax_type_id"
            class="flex items-center justify-between w-full"
          >
            <label
              class="m-0 text-sm font-semibold leading-5 text-gray-500 uppercase"
              >{{ tax.name }} - {{ tax.percent }}%
            </label>
            <label
              class="flex items-center justify-center m-0 text-lg text-black uppercase"
              style="font-size: 18px"
            >
              <div v-html="$utils.formatMoney(tax.amount, currency)" />
            </label>
          </div>
          <div
            v-if="discountPerItem === 'NO' || discountPerItem === null"
            class="flex items-center justify-between w-full mt-2"
          >
            <label
              class="text-sm font-semibold leading-5 text-gray-500 uppercase"
              >{{ $t('invoices.discount') }}</label
            >
            <div class="flex" style="width: 105px" role="group">
              <sw-input
                v-model="discount"
                :invalid="$v.formData.discount_val.$error"
                class="border-r-0 rounded-tr-sm rounded-br-sm"
                @input="$v.formData.discount_val.$touch()"
              />
              <sw-dropdown position="bottom-end">
                <sw-button
                  slot="activator"
                  type="button"
                  data-toggle="dropdown"
                  size="discount"
                  aria-haspopup="true"
                  aria-expanded="false"
                  style="height: 43px"
                  variant="white"
                >
                  <span class="flex">
                    {{
                      formData.discount_type == 'fixed'
                        ? currency.symbol
                        : '%'
                    }}
                    <chevron-down-icon class="h-5" />
                  </span>
                </sw-button>

                <sw-dropdown-item @click="selectFixed">
                  {{ $t('general.fixed') }}
                </sw-dropdown-item>

                <sw-dropdown-item @click="selectPercentage">
                  {{ $t('general.percentage') }}
                </sw-dropdown-item>
              </sw-dropdown>
            </div>
          </div>

          <div v-if="taxPerItem ? 'NO' : null">
            <tax
              v-for="(tax, index) in formData.taxes"
              :index="index"
              :total="subtotalWithDiscount"
              :key="tax.id"
              :tax="tax"
              :taxes="formData.taxes"
              :currency="currency"
              :total-tax="totalSimpleTax"
              @remove="removeInvoiceTax"
              @update="updateTax"
            />
          </div>

          <sw-popup
            v-if="taxPerItem === 'NO' || taxPerItem === null"
            ref="taxModal"
            class="my-3 text-sm font-semibold leading-5 text-primary-400"
          >
            <div slot="activator" class="float-right pt-2 pb-5">
              + {{ $t('invoices.add_tax') }}
            </div>
            <tax-select-popup :taxes="formData.taxes" @select="onSelectTax" />
          </sw-popup>

          <div
            class="flex items-center justify-between w-full pt-2 mt-5 border-t border-gray-200 border-solid"
          >
            <label
              class="text-sm font-semibold leading-5 text-gray-500 uppercase"
            >
              {{ $t('invoices.total') }} {{ $t('invoices.amount') }}:
            </label>
            <label
              class="flex items-center justify-center text-lg uppercase text-primary-400"
            >
              <div v-html="$utils.formatMoney(total, currency)" />
            </label>
          </div>
        </div>
        <div class="block mt-2 md:hidden">
          <sw-button
            :disabled="isLoading"
            :loading="isLoading"
            :tabindex="6"
            variant="primary"
            type="submit"
            size="lg"
            class="flex w-full"
          >
            <save-icon v-if="!isLoading" class="mr-2 -ml-1" />
            {{
              isEdit
                ? $t('expenses.update_expense')
                : $t('expenses.save_expense')
            }}
          </sw-button>
        </div>
      </div>
    </form>
    <base-loader v-else />
  </base-page>
</template>

<script>
import draggable from 'vuedraggable'
import InvoiceItem from './Item'
import InvoiceStub from '../../stub/invoice'
import { mapActions, mapGetters } from 'vuex'
import moment from 'moment'
import Guid from 'guid'
import TaxStub from '../../stub/tax'
import Tax from './InvoiceTax'
import { PlusSmIcon, DownloadIcon } from '@vue-hero-icons/outline'
import {
  ChevronDownIcon,
  PencilIcon,
  ShoppingCartIcon,
  HashtagIcon,
  CloudUploadIcon,
  TrashIcon,
} from '@vue-hero-icons/solid'
import CustomFieldsMixin from '../../mixins/customFields'
import invoice from '../../stub/invoice'

const {
  required,
  between,
  maxLength,
  numeric,
} = require('vuelidate/lib/validators')

export default {
  components: {
    InvoiceItem,
    Tax,
    draggable,
    PlusSmIcon,
    ChevronDownIcon,
    PencilIcon,
    ShoppingCartIcon,
    HashtagIcon,
    CloudUploadIcon,
    TrashIcon,
    DownloadIcon,
  },
  mixins: [CustomFieldsMixin],

  props: {
    addname: {
      type: String,
      default: '',
    },
  },

  data() {
    return {
      formData: {
        expense_date: null,
        doc_num: null,
        expense_due: null,
        // invoice_number: null,
        user_id: null,
        // invoice_template_id: 1,
        sub_total: null,
        total: null,
        tax: null,
        notes: null,
        discount_type: 'fixed',
        discount_val: 0,
        discount: 0,
        // reference_number: null,
        items: [
          {
            ...InvoiceStub,
            id: Guid.raw(),
            taxes: [{ ...TaxStub, id: Guid.raw() }],
          },
        ],
        taxes: [],
      },
      selectedCurrency: '',
      taxPerItem: null,
      discountPerItem: null,
      // isLoadingInvoice: false,
      isLoadingData: false,
      isLoading: false,
      maxDiscount: 0,
      // invoicePrefix: null,
      // invoiceNumAttribute: null,
      // InvoiceFields: [
      //   'customer',
      //   'customerCustom',
      //   'company',
      //   'invoice',
      //   'invoiceCustom',
      // ],
      // customerId: null,
      supplier: null,
      isRequestOnGoing: false,
      category: null,
      previewReceipt: null,
      isReceiptAvailable: false,
    }
  },

  validations() {
    return {
      category: {
        required,
      },

      supplier: {
        required,
      },

      formData: {
        expense_date: {
          required,
        },
        doc_num: {
          maxLength: maxLength(255),
        },
        expense_due: {
          required,
        },
        discount_val: {
          between: between(0, this.subtotal),
        },
        // reference_number: {
        //   maxLength: maxLength(255),
        // },
        notes: {
          maxLength: maxLength(65000),
        },
      },
      // selectedCustomer: {
      //   required,
      // },
      // invoiceNumAttribute: {
      //   required,
      //   numeric,
      // },
    }
  },

  computed: {
    ...mapGetters('company', ['itemDiscount']),

    ...mapGetters('company', ['defaultCurrency']),

    ...mapGetters('notes', ['notes']),

    ...mapGetters('invoice', [
      'getTemplateId',
      'selectedCustomer',
      'selectedNote',
    ]),

    ...mapGetters('invoiceTemplate', ['getInvoiceTemplates']),

    currency() {
      return this.selectedCurrency
    },

    pageTitle() {
      if (this.isEdit) {
        return this.$t('expenses.edit_expense')
      }
      return this.$t('expenses.new_expense')
    },

    isEdit() {
      if (this.$route.name === 'expenses.edit') {
        return true
      }
      return false
    },

    ...mapGetters('category', ['categories']),

    ...mapGetters('supplier', ['suppliers']),

    ...mapGetters('company', ['getSelectedCompany']),

    getReceiptUrl() {
      if (this.isEdit) {
        return `/expenses/${this.$route.params.id}/receipt`
      }
    },

    subtotalWithDiscount() {
      return this.subtotal - this.formData.discount_val
    },

    total() {
      return this.subtotalWithDiscount + this.totalTax
    },

    subtotal() {
      return this.formData.items.reduce(function (a, b) {
        return a + b['total']
      }, 0)
    },

    discount: {
      get: function () {
        return this.formData.discount
      },
      set: function (newValue) {
        if (this.formData.discount_type === 'percentage') {
          this.formData.discount_val = (this.subtotal * newValue) / 100
        } else {
          this.formData.discount_val = Math.round(newValue * 100)
        }

        this.formData.discount = newValue
      },
    },

    totalSimpleTax() {
      return Math.round(
        window._.sumBy(this.formData.taxes, function (tax) {
          if (!tax.compound_tax) {
            return tax.amount
          }

          return 0
        })
      )
    },

    totalCompoundTax() {
      return Math.round(
        window._.sumBy(this.formData.taxes, function (tax) {
          if (tax.compound_tax) {
            return tax.amount
          }

          return 0
        })
      )
    },

    totalTax() {
      if (this.taxPerItem === 'NO' || this.taxPerItem === null) {
        return this.totalSimpleTax + this.totalCompoundTax
      }

      return Math.round(
        window._.sumBy(this.formData.items, function (tax) {
          return tax.tax
        })
      )
    },

    allTaxes() {
      let taxes = []

      this.formData.items.forEach((item) => {
        item.taxes.forEach((tax) => {
          let found = taxes.find((_tax) => {
            return _tax.tax_type_id === tax.tax_type_id
          })

          if (found) {
            found.amount += tax.amount
          } else if (tax.tax_type_id) {
            taxes.push({
              tax_type_id: tax.tax_type_id,
              amount: tax.amount,
              percent: tax.percent,
              name: tax.name,
            })
          }
        })
      })

      return taxes
    },

    categoryError() {
      if (!this.$v.category.$error) {
        return ''
      }
      if (!this.$v.category.required) {
        return this.$t('validation.required')
      }
    },

    supplierError() {
      if (!this.$v.supplier.$error) {
        return ''
      }
      if (!this.$v.supplier.required) {
        return this.$t('validation.required')
      }
    },
    notesError() {
      if (!this.$v.formData.notes.$error) {
        return ''
      }
      if (!this.$v.formData.notes.maxLength) {
        return this.$t('validation.notes_maxlength')
      }
    },

    docNumError() {
      if (!this.$v.formData.doc_num.$error) {
        return ''
      }

      if (!this.$v.formData.doc_num.required) {
        return this.$t('validation.required')
      }

      if (!this.$v.formData.doc_num.maxLength) {
        return this.$tc('validation.doc_num_maxlength')
      }
    },

    invoiceDateError() {
      if (!this.$v.formData.expense_date.$error) {
        return ''
      }
      if (!this.$v.formData.expense_date.required) {
        return this.$t('validation.required')
      }
    },

    dueDateError() {
      if (!this.$v.formData.expense_due.$error) {
        return ''
      }
      if (!this.$v.formData.expense_due.required) {
        return this.$t('validation.required')
      }
    },

    // invoiceNumError() {
    //   if (!this.$v.invoiceNumAttribute.$error) {
    //     return ''
    //   }

    //   if (!this.$v.invoiceNumAttribute.required) {
    //     return this.$tc('validation.required')
    //   }

    //   if (!this.$v.invoiceNumAttribute.numeric) {
    //     return this.$tc('validation.numbers_only')
    //   }
    // },

    // referenceError() {
    //   if (!this.$v.formData.reference_number.$error) {
    //     return ''
    //   }

    //   if (!this.$v.formData.reference_number.maxLength) {
    //     return this.$tc('validation.ref_number_maxlength')
    //   }
    // },
  },

  watch: {
    category(newValue) {
      this.formData.expense_category_id = newValue.id
    },

    // selectedCustomer(newVal) {
    //   if (newVal && newVal.currency) {
    //     this.selectedCurrency = newVal.currency
    //   } else {
    //     this.selectedCurrency = this.defaultCurrency
    //   }
    // },

    // selectedNote() {
    //   if (this.selectedNote) {
    //     this.formData.notes = this.selectedNote
    //   }
    // },

    subtotal(newValue) {
      if (this.formData.discount_type === 'percentage') {
        this.formData.discount_val = (this.formData.discount * newValue) / 100
      }
    },
  },

  created() {
    this.loadData()

    this.fetchInitialData()

    // window.hub.$on('newTax', this.onSelectTax)
    // if (this.$route.query.customer) {
    //   this.customerId = parseInt(this.$route.query.customer)
    // }

    window.hub.$on('newCategory', (val) => {
      this.category = val
    })
  },

  methods: {
    ...mapActions('modal', ['openModal']),

    ...mapActions('expense', [
      'getExpenseReceipt',
      'addExpense',
      'fetchExpense',
      'updateExpense',
    ]),

    ...mapActions('modal', ['openModal']),

    ...mapActions('company', ['fetchCompanySettings']),

    ...mapActions('item', ['fetchItems']),

    ...mapActions('taxType', ['fetchTaxTypes']),

    ...mapActions('category', ['fetchCategories']),

    ...mapActions('supplier', ['fetchSuppliers']),

    openCategoryModal() {
      this.openModal({
        title: this.$t('settings.expense_category.add_category'),
        componentName: 'CategoryModal',
      })
    },

    onChange(data) {
      this.previewReceipt = data.image
      this.fileObject = data.file
    },

    async getReceipt() {
      let res = await this.getExpenseReceipt(this.$route.params.id)

      if (res.data.error) {
        this.isReceiptAvailable = false
        return true
      }

      this.isReceiptAvailable = true
      this.previewReceipt = res.data.image
    },

    setExpenseSupplier(id) {
      this.supplier = this.suppliers.find((c) => {
        return c.id == id
      })
    },

    selectFixed() {
      if (this.formData.discount_type === 'fixed') {
        return
      }

      this.formData.discount_val = Math.round(this.formData.discount * 100)
      this.formData.discount_type = 'fixed'
    },

    onRemoveFile() {
      this.previewReceipt = null
      this.fileObject = null
    },

    selectPercentage() {
      if (this.formData.discount_type === 'percentage') {
        return
      }

      this.formData.discount_val =
        (this.subtotal * this.formData.discount) / 100

      this.formData.discount_type = 'percentage'
    },

    updateTax(data) {
      Object.assign(this.formData.taxes[data.index], { ...data.item })
    },

    async fetchInitialData() {
      this.isLoadingData = true

      if (!this.isEdit) {
        let response = await this.fetchCompanySettings([
          'discount_per_item',
          'tax_per_item',
        ])

        if (response.data) {
          this.discountPerItem = response.data.discount_per_item
          this.taxPerItem = response.data.tax_per_item
        }
      }

      Promise.all([
        this.fetchItems({
          filter: {},
          orderByField: '',
          orderBy: '',
        }),
        // this.fetchInvoiceTemplates(),
        // this.resetSelectedNote(),
        // this.getInvoiceNumber(),
        this.fetchCompanySettings(['invoice_auto_generate']),
      ])
        .then(async ([res1, res2, res3, res4, res5]) => {
          if (
            !this.isEdit &&
            res5.data &&
            res5.data.invoice_auto_generate === 'YES'
          ) {
            if (res4.data) {
              // this.invoiceNumAttribute = res4.data.nextNumber
              // this.invoicePrefix = res4.data.prefix
            }
          } else {
            // this.invoicePrefix = res4.data.prefix
          }

          // this.discountPerItem = res5.data.discount_per_item
          // this.taxPerItem = res5.data.tax_per_item
          this.isLoadingData = false
        })
        .catch((error) => {
          console.log(error)
        })
    },

    async loadData() {
      this.isRequestOnGoing = true
      await this.fetchCategories({ limit: 'all' })
      await this.fetchSuppliers({ limit: 'all' })
      if (this.isEdit) {
        this.isRequestOnGoing = true
        // this.isLoadingInvoice = true

        Promise.all([
          this.fetchExpense(this.$route.params.id),
          this.fetchCustomFields({
            type: 'Expense',
            limit: 'all',
          }),
          this.fetchTaxTypes({ limit: 'all' }),
        ])
          .then(async ([res1, res2]) => {
            if (res1.data) {
              // this.customerId = res1.data.invoice.user_id
              // this.formData = res1.data.expense
              this.formData = { ...this.formData, ...res1.data.expense }

              this.formData.expense_date = moment(
                res1.data.expense.expense_date,
                'YYYY-MM-DD'
              ).toString()

              this.formData.expense_due = moment(
                res1.data.expense.expense_due,
                'YYYY-MM-DD'
              ).toString()

              if (res1.data.expense.expense_category_id) {
                this.category = this.categories.find(
                  (category) =>
                    category.id === res1.data.expense.expense_category_id
                )
              }

              if (res1.data.expense.user_id) {
                this.supplier = this.suppliers.find(
                  (supplier) => supplier.id === res1.data.expense.user_id
                )
              }

              this.discountPerItem = res1.data.expense.discount_per_item
              this.selectedCurrency = this.defaultCurrency
              this.invoiceNumAttribute = res1.data.nextInvoiceNumber
              this.invoicePrefix = res1.data.invoicePrefix
              this.taxPerItem = res1.data.expense.tax_per_item
              let fields = res1.data.expense.fields

              if (res2.data) {
                let customFields = res2.data.customFields.data
                this.setEditCustomFields(fields, customFields)
              }
            }

            this.getReceipt()
            // this.isLoadingInvoice = false
            this.isRequestOnGoing = false
          })
          .catch((error) => {
            console.log(error)
          })

        return true
      }

      this.isRequestOnGoing = true
      await this.setInitialCustomFields('Invoice')
      await this.fetchTaxTypes({ limit: 'all' })

      if (this.$route.query.supplier) {
        this.setExpenseSupplier(parseInt(this.$route.query.supplier))
      }

      this.selectedCurrency = this.defaultCurrency
      this.formData.expense_date = moment().toString()
      this.formData.expense_due = moment().add(1, 'days').toString()

      // this.isLoadingInvoice = false
      this.isRequestOnGoing = false
    },

    // openTemplateModal() {
    //   this.openModal({
    //     title: this.$t('general.choose_template'),
    //     componentName: 'InvoiceTemplate',
    //     data: this.getInvoiceTemplates,
    //   })
    // },

    addItem() {
      this.formData.items.push({
        ...InvoiceStub,
        id: Guid.raw(),
        taxes: [{ ...TaxStub, id: Guid.raw() }],
      })
    },

    removeItem(index) {
      this.formData.items.splice(index, 1)
    },

    updateItem(data) {
      Object.assign(this.formData.items[data.index], { ...data.item })
    },

    async submitForm() {
      // return
      let validate = await this.touchCustomField()
      this.$v.category.$touch()
      this.$v.formData.$touch()

      if (!this.checkValid() || validate.error) {
        return false
      }

      let data = new FormData()

      if (this.fileObject) {
        data.append('attachment_receipt', this.fileObject)
      }

      data.append('expense_category_id', this.formData.expense_category_id)
      data.append(
        'expense_date',
        moment(this.formData.expense_date).format('YYYY-MM-DD')
      )
      data.append(
        'expense_due',
        moment(this.formData.expense_due).format('YYYY-MM-DD')
      )
      data.append('notes', this.formData.notes ? this.formData.notes : '')
      data.append('user_id', this.supplier ? this.supplier.id : '')
      data.append('customFields', JSON.stringify(this.formData.customFields))
      data.append('sub_total', this.subtotal)
      data.append('total', this.total)
      data.append('tax', this.totalTax)
      data.append('taxes', JSON.stringify(this.formData.taxes))
      data.append('doc_num', this.formData.doc_num)
      data.append('items', JSON.stringify(this.formData.items))
      data.append('discount', this.formData.discount)
      data.append('discount_type', this.formData.discount_type)
      data.append('discount_val', this.formData.discount_val)
      // this.isLoading = true
      // this.formData.invoice_number =
      //   this.invoicePrefix + '-' + this.invoiceNumAttribute

      // let data = {
      //   ...this.formData,
      //   ...this.formData,
      //   sub_total: this.subtotal,
      //   total: this.total,
      //   tax: this.totalTax,
      //   user_id: null,
      //   invoice_template_id: this.getTemplateId,
      // }

      // if (this.selectedCustomer != null) {
      //   data.user_id = this.selectedCustomer.id
      // }

      // if (this.$route.name === 'sales.edit') {
      //   this.submitUpdate(data)
      //   return
      // }

      // this.submitCreate(data)
      if (this.isEdit) {
        this.isLoading = true
        data.append('_method', 'PUT')
        let response = await this.updateExpense({
          id: this.$route.params.id,
          editData: data,
        })

        if (response.data.success) {
          this.isLoading = false
          window.toastr['success'](this.$t('expenses.updated_message'))
          this.$router.push('/admin/expenses')
          return true
        }
        window.toastr['error'](response.data.error)
      } else {
        this.isLoading = true
        let response = await this.addExpense(data)
        this.isLoading = false

        if (response.data.success) {
          window.toastr['success'](this.$t('expenses.created_message'))
          this.$router.push('/admin/expenses')
          return true
        }
        window.toastr['success'](response.data.success)
      }
    },

    // submitCreate(data) {
    //   this.addInvoice(data)
    //     .then((res) => {
    //       if (res.data) {
    //         this.$router.push(`/admin/sales/${res.data.invoice.id}/view`)

    //         window.toastr['success'](this.$t('invoices.created_message'))
    //       }

    //       this.isLoading = false
    //     })
    //     .catch((err) => {
    //       this.isLoading = false
    //     })
    // },

    // submitUpdate(data) {
    //   this.updateInvoice(data)
    //     .then((res) => {
    //       this.isLoading = false
    //       if (res.data.success) {
    //         this.$router.push(`/admin/sales/${res.data.invoice.id}/view`)
    //         window.toastr['success'](this.$t('invoices.updated_message'))
    //       }

    //       if (res.data.error === 'invalid_due_amount') {
    //         window.toastr['error'](
    //           this.$t('invoices.invalid_due_amount_message')
    //         )
    //       }
    //     })
    //     .catch((err) => {
    //       this.isLoading = false
    //     })
    // },

    checkItemsData(index, isValid) {
      this.formData.items[index].valid = isValid
    },

    onSelectTax(selectedTax) {
      let amount = 0

      if (selectedTax.compound_tax && this.subtotalWithDiscount) {
        amount = Math.round(
          ((this.subtotalWithDiscount + this.totalSimpleTax) *
            selectedTax.percent) /
            100
        )
      } else if (this.subtotalWithDiscount && selectedTax.percent) {
        amount = Math.round(
          (this.subtotalWithDiscount * selectedTax.percent) / 100
        )
      }

      this.formData.taxes.push({
        ...TaxStub,
        id: Guid.raw(),
        name: selectedTax.name,
        percent: selectedTax.percent,
        compound_tax: selectedTax.compound_tax,
        tax_type_id: selectedTax.id,
        amount,
      })

      if (this.$refs) {
        this.$refs.taxModal.close()
      }
    },

    removeInvoiceTax(index) {
      this.formData.taxes.splice(index, 1)
    },

    checkValid() {
      this.$v.formData.$touch()
      this.$v.supplier.$touch()
      // this.$v.invoiceNumAttribute.$touch()

      window.hub.$emit('checkItems')
      let isValid = true
      this.formData.items.forEach((item) => {
        if (!item.valid) {
          isValid = false
        }
      })
      if (
        !this.$v.supplier.$invalid &&
        this.$v.formData.$invalid === false &&
        isValid === true
      ) {
        return true
      }
      return false
    },
    // onSelectNote(data) {
    //   this.formData.notes = '' + data.notes
    //   this.$refs.notePopup.close()
    // },
  },
}
</script>

<style lang="scss">
.invoice-create-page {
  .invoice-foot {
    .invoice-total {
      min-width: 390px;
    }
  }
  @media (max-width: 480px) {
    .invoice-foot {
      .invoice-total {
        min-width: 384px;
      }
    }
  }
}
</style>
