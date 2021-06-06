<template>
  <div
    class="pt-6 mt-5 border-t-2 border-solid lg:pt-8 md:pt-4"
    style="border-top-color: #f9fbff"
  >
    <div class="col-span-12">
      <p class="text-gray-500 uppercase sw-section-title">
        {{ $t('suppliers.basic_info') }}
      </p>
      <div
        class="grid grid-cols-1 gap-4 mt-5 lg:grid-cols-3 md:grid-cols-2 sm:grid-cols-1"
      >
        <div>
          <p
            class="mb-1 text-sm font-normal leading-5 non-italic text-primary-800"
          >
            {{ $t('suppliers.display_name') }}
          </p>
          <p class="text-sm font-bold leading-5 text-black non-italic">
            {{
              selectedViewSupplier.supplier &&
              selectedViewSupplier.supplier.name
                ? selectedViewSupplier.supplier.name
                : ''
            }}
          </p>
        </div>
        <div>
          <p
            class="mb-1 text-sm font-normal leading-5 non-italic text-primary-800"
          >
            {{ $t('suppliers.primary_contact_name') }}
          </p>
          <p class="text-sm font-bold leading-5 text-black non-italic">
            {{
              selectedViewSupplier.supplier &&
              selectedViewSupplier.supplier.contact_name
                ? selectedViewSupplier.supplier.contact_name
                : ''
            }}
          </p>
        </div>
        <div>
          <p
            class="mb-1 text-sm font-normal leading-5 non-italic text-primary-800"
          >
            {{ $t('suppliers.email') }}
          </p>
          <p class="text-sm font-bold leading-5 text-black non-italic">
            {{
              selectedViewSupplier.supplier &&
              selectedViewSupplier.supplier.email
                ? selectedViewSupplier.supplier.email
                : ''
            }}
          </p>
        </div>
      </div>
      <div
        class="grid grid-cols-1 gap-4 mt-5 lg:grid-cols-3 md:grid-cols-2 sm:grid-cols-1"
      >
        <div>
          <p
            class="mb-1 text-sm font-normal leading-5 non-italic text-primary-800"
          >
            {{ $t('wizard.currency') }}
          </p>
          <p class="text-sm font-bold leading-5 text-black non-italic">
            {{
              selectedViewSupplier.supplier.currency
                ? `${selectedViewSupplier.supplier.currency.code} (${selectedViewSupplier.supplier.currency.symbol})`
                : ''
            }}
          </p>
        </div>
        <div>
          <p
            class="mb-1 text-sm font-normal leading-5 non-italic text-primary-800"
          >
            {{ $t('suppliers.phone_number') }}
          </p>
          <p class="text-sm font-bold leading-5 text-black non-italic">
            {{
              selectedViewSupplier.supplier &&
              selectedViewSupplier.supplier.phone
                ? selectedViewSupplier.supplier.phone
                : ''
            }}
          </p>
        </div>
        <div>
          <p
            class="mb-1 text-sm font-normal leading-5 non-italic text-primary-800"
          >
            {{ $t('suppliers.website') }}
          </p>
          <p class="text-sm font-bold leading-5 text-black non-italic">
            {{
              selectedViewSupplier.supplier &&
              selectedViewSupplier.supplier.website
                ? selectedViewSupplier.supplier.website
                : ''
            }}
          </p>
        </div>
      </div>

      <p
        v-if="
          getFormattedShippingAddress.length ||
          getFormattedBillingAddress.length
        "
        class="mt-8 text-gray-500 uppercase sw-section-title"
      >
        {{ $t('suppliers.address') }}
      </p>
      <div
        class="grid grid-cols-1 gap-4 md:grid-cols-2 sm:grid-cols-1 lg:grid-cols-2"
      >
        <div v-if="getFormattedBillingAddress.length" class="mt-5">
          <p
            class="mb-1 text-sm font-normal leading-5 non-italic text-primary-800"
          >
            {{ $t('suppliers.billing_address') }}
          </p>
          <p
            class="text-sm font-bold leading-5 text-black non-italic"
            v-html="getFormattedBillingAddress"
          />
        </div>
        <div v-if="getFormattedShippingAddress.length" class="mt-5">
          <p
            class="mb-1 text-sm font-normal leading-5 non-italic text-primary-800"
          >
            {{ $t('suppliers.shipping_address') }}
          </p>
          <p
            class="text-sm font-bold leading-5 text-black non-italic"
            v-html="getFormattedShippingAddress"
          />
        </div>
      </div>

      <!-- Custom Fields -->
      <p
        v-if="getCustomField.length > 0"
        class="mt-8 text-gray-500 uppercase sw-section-title"
      >
        {{ $t('settings.custom_fields.title') }}
      </p>

      <div
        class="grid grid-cols-1 gap-4 mt-5 lg:grid-cols-3 md:grid-cols-2 sm:grid-cols-1"
      >
        <div
          v-for="(field, index) in getCustomField"
          :key="index"
          :required="field.is_required ? true : false"
        >
          <p
            class="mb-1 text-sm font-normal leading-5 non-italic text-primary-800"
          >
            {{ field.custom_field.label }}
          </p>
          <p
            v-if="field.type === 'Switch'"
            class="text-sm font-bold leading-5 text-black non-italic"
          >
            <span v-if="field.defaultAnswer === 1"> Yes </span>
            <span v-else> No </span>
          </p>
          <p v-else class="text-sm font-bold leading-5 text-black non-italic">
            {{ field.defaultAnswer }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
  data() {
    return {
      supplier: null,
      customFields: [],
    }
  },
  computed: {
    getFormattedBillingAddress() {
      let billingAddress = ``

      if (!this.selectedViewSupplier.supplier) {
        return billingAddress
      }

      if (!this.selectedViewSupplier.supplier.billing_address) {
        return billingAddress
      }

      if (this.selectedViewSupplier.supplier.billing_address.address_street_1) {
        billingAddress += `<span>${this.selectedViewSupplier.supplier.billing_address.address_street_1},</span><br>`
      }
      if (this.selectedViewSupplier.supplier.billing_address.address_street_2) {
        billingAddress += `<span>${this.selectedViewSupplier.supplier.billing_address.address_street_2},</span><br>`
      }
      if (this.selectedViewSupplier.supplier.billing_address.city) {
        billingAddress += `<span>${this.selectedViewSupplier.supplier.billing_address.city},</span> `
      }
      if (this.selectedViewSupplier.supplier.billing_address.state) {
        billingAddress += `<span>${this.selectedViewSupplier.supplier.billing_address.state},</span><br>`
      }
      if (this.selectedViewSupplier.supplier.billing_address.country) {
        billingAddress += `<span>${this.selectedViewSupplier.supplier.billing_address.country.name}.</span> `
      }
      if (this.selectedViewSupplier.supplier.billing_address.zip) {
        billingAddress += `<span>${this.selectedViewSupplier.supplier.billing_address.zip}.</span> `
      }
      return billingAddress
    },
    getFormattedShippingAddress() {
      let shippingAddress = ``

      if (!this.selectedViewSupplier.supplier) {
        return shippingAddress
      }

      if (!this.selectedViewSupplier.supplier.shipping_address) {
        return shippingAddress
      }

      if (
        this.selectedViewSupplier.supplier.shipping_address.address_street_1
      ) {
        shippingAddress += `<span>${this.selectedViewSupplier.supplier.shipping_address.address_street_1},</span><br>`
      }
      if (
        this.selectedViewSupplier.supplier.shipping_address.address_street_2
      ) {
        shippingAddress += `<span>${this.selectedViewSupplier.supplier.shipping_address.address_street_2},</span><br>`
      }
      if (this.selectedViewSupplier.supplier.shipping_address.city) {
        shippingAddress += `<span>${this.selectedViewSupplier.supplier.shipping_address.city},</span> `
      }
      if (this.selectedViewSupplier.supplier.shipping_address.state) {
        shippingAddress += `<span>${this.selectedViewSupplier.supplier.shipping_address.state},</span><br>`
      }
      if (this.selectedViewSupplier.supplier.shipping_address.country) {
        shippingAddress += `<span>${this.selectedViewSupplier.supplier.shipping_address.country.name}.</span> `
      }
      if (this.selectedViewSupplier.supplier.shipping_address.zip) {
        shippingAddress += `<span>${this.selectedViewSupplier.supplier.shipping_address.zip}.</span> `
      }
      return shippingAddress
    },

    getCustomField() {
      if (this.selectedViewSupplier.supplier.fields) {
        return this.selectedViewSupplier.supplier.fields
      }
      return []
    },

    ...mapGetters('supplier', ['selectedViewSupplier']),
  },
  watch: {
    $route(to, from) {
      this.supplier = this.selectedViewSupplier
    },
  },
}
</script>
