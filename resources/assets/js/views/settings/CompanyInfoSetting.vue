<template>
  <form @submit.prevent="updateCompanyData" class="relative h-full">
    <base-loader v-if="isRequestOnGoing" :show-bg-overlay="true" />
    <sw-card variant="setting-card">
      <template slot="header">
        <h6 class="sw-section-title">
          {{ $t('settings.company_info.company_info') }}
        </h6>
        <p
          class="mt-2 text-sm leading-snug text-gray-500"
          style="max-width: 680px"
        >
          {{ $t('settings.company_info.section_description') }}
        </p>
      </template>

      <div class="grid mb-6 md:grid-cols-2">
        <sw-input-group :label="$tc('settings.company_info.company_logo')">
          <div
            id="logo-box"
            class="relative flex items-center justify-center h-24 p-5 mt-2 bg-transparent border-2 border-gray-200 border-dashed rounded-md image-upload-box"
          >
            <img
              v-if="previewLogo"
              :src="previewLogo"
              class="absolute opacity-100 preview-logo"
              style="max-height: 80%; animation: fadeIn 2s ease"
            />
            <div v-else class="flex flex-col items-center">
              <cloud-upload-icon
                class="h-5 mb-2 text-xl leading-6 text-gray-400"
              />
              <p class="text-xs leading-4 text-center text-gray-400">
                Arrastre un archivo aquí o
                <span id="pick-avatar" class="cursor-pointer text-primary-500">
                  navegar
                </span>
                elegir un archivo
              </p>
            </div>
          </div>

          <sw-avatar
            trigger="#logo-box"
            :preview-avatar="previewLogo"
            :labels="croperLabels"
            @changed="onChange"
            @uploadHandler="onUploadHandler"
            @handleUploadError="onHandleUploadError"
          >
            <template v-slot:icon>
              <cloud-upload-icon
                class="h-5 mb-2 text-xl leading-6 text-gray-400"
              />
            </template>
          </sw-avatar>
        </sw-input-group>
      </div>

      <div class="grid gap-6 sm:grid-col-1 md:grid-cols-2">
        <sw-input-group
          :label="$tc('settings.company_info.company_name')"
          :error="nameError"
          required
        >
          <sw-input
            v-model="formData.name"
            :invalid="$v.formData.name.$error"
            :placeholder="$t('settings.company_info.company_name')"
            class="mt-2"
            @input="$v.formData.name.$touch()"
          />
        </sw-input-group>

        <sw-input-group :label="$tc('settings.company_info.phone')">
          <sw-input
            v-model="formData.phone"
            class="mt-2"
            :placeholder="$t('settings.company_info.phone')"
          />
        </sw-input-group>

        <!-- <sw-input-group
          :label="$tc('settings.company_info.country')"
          :error="countryError"
          required
        >
          <sw-select
            v-model="country"
            :options="countries"
            :class="{ error: $v.formData.country_id.$error }"
            :searchable="true"
            :show-labels="false"
            :allow-empty="false"
            :placeholder="$t('general.select_country')"
            class="mt-2"
            label="name"
            track-by="id"
          />
        </sw-input-group> -->

        <sw-input-group :label="$tc('settings.company_info.state')">
          <sw-input
            v-model="formData.state"
            :placeholder="$tc('settings.company_info.state')"
            name="state"
            class="mt-2"
            type="text"
          />
        </sw-input-group>

        <sw-input-group :label="$tc('settings.company_info.city')">
          <sw-input
            v-model="formData.city"
            :placeholder="$tc('settings.company_info.city')"
            name="city"
            class="mt-2"
            type="text"
          />
        </sw-input-group>

        <sw-input-group :label="$tc('settings.company_info.zip')">
          <sw-input
            v-model="formData.zip"
            :placeholder="$tc('settings.company_info.zip')"
            class="mt-2"
          />
        </sw-input-group>

        <sw-input-group :label="$tc('settings.company_info.cif')" :error="cifError" required>
          <sw-input
            v-model="formData.cif"
            :invalid="$v.formData.cif.$error"
            :placeholder="$tc('settings.company_info.company_tax_id_number_placeholder')"
            name="city"
            class="mt-2"
            type="text"
          />
        </sw-input-group>
        
        <div>
          <sw-input-group
            :label="$tc('settings.company_info.address')"
            :error="address1Error"
          >
            <sw-textarea
              v-model="formData.address_street_1"
              :placeholder="$tc('general.street_1')"
              :class="{ invalid: $v.formData.address_street_1.$error }"
              rows="2"
              @input="$v.formData.address_street_1.$touch()"
            />
          </sw-input-group>

          <sw-input-group :error="address2Error" class="my-2">
            <sw-textarea
              v-model="formData.address_street_2"
              :placeholder="$tc('general.street_2')"
              :class="{ invalid: $v.formData.address_street_2.$error }"
              rows="2"
              @input="$v.formData.address_street_2.$touch()"
            />
          </sw-input-group>
        </div>
      </div>

      <sw-button
        class="mt-4"
        :loading="isLoading"
        :disabled="isLoading"
        variant="primary"
      >
        <save-icon v-if="!isLoading" class="mr-2 -ml-1" />
        {{ $tc('settings.company_info.save') }}
      </sw-button>
    </sw-card>
  </form>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import { CloudUploadIcon } from '@vue-hero-icons/solid'
const { required, email, maxLength } = require('vuelidate/lib/validators')

export default {
  components: {
    CloudUploadIcon,
  },
  data() {
    return {
      isFetchingData: false,
      formData: {
        name: null,
        email: '',
        phone: '',
        zip: '',
        address_street_1: '',
        address_street_2: '',
        website: '',
        // country_id: null,
        state: '',
        city: '',
        cif: '',
      },
      isLoading: false,
      country: null,
      passData: [],
      fileSendUrl: '/api/v1/settings/company',
      previewLogo: null,
      fileObject: null,
      cropperOutputMime: '',
      isRequestOnGoing: false,
      croperLabels: {
        submit: 'Colocar',
        cancel: 'Cancelar'
      },
    }
  },
  watch: {
    country(newCountry) {
      this.formData.country_id = newCountry.id
      if (this.isFetchingData) {
        return true
      }
    },
  },
  validations: {
    formData: {
      name: {
        required,
      },
      // country_id: {
      //   required,
      // },
      email: {
        email,
      },
      address_street_1: {
        maxLength: maxLength(255),
      },
      address_street_2: {
        maxLength: maxLength(255),
      },
      cif: {
        required,
      },
    },
  },
  computed: {
    ...mapGetters(['countries']),
    nameError() {
      if (!this.$v.formData.name.$error) {
        return ''
      }
      if (!this.$v.formData.name.required) {
        return this.$tc('validation.required')
      }
    },
    cifError() {
      if (!this.$v.formData.cif.$error) {
        return ''
      }
      if (!this.$v.formData.cif.required) {
        return this.$tc('validation.required')
      }
    },
    // countryError() {
    //   if (!this.$v.formData.country_id.$error) {
    //     return ''
    //   }
    //   if (!this.$v.formData.country_id.required) {
    //     return this.$tc('validation.required')
    //   }
    // },
    address1Error() {
      if (!this.$v.formData.address_street_1.$error) {
        return ''
      }

      if (!this.$v.formData.address_street_1.maxLength) {
        return this.$tc('validation.address_maxlength')
      }
    },
    address2Error() {
      if (!this.$v.formData.address_street_2.$error) {
        return ''
      }

      if (!this.$v.formData.address_street_2.maxLength) {
        return this.$tc('validation.address_maxlength')
      }
    },
  },
  mounted() {
    this.setInitialData()
  },
  methods: {
    ...mapActions('company', ['updateCompany', 'updateCompanyLogo']),
    ...mapActions('user', ['fetchCurrentUser']),
    onUploadHandler(cropper) {
      this.previewLogo = cropper
        .getCroppedCanvas()
        .toDataURL(this.cropperOutputMime)
    },
    onHandleUploadError() {
      window.toastr['error']('Oops! Something went wrong...')
    },
    onChange(file) {
      this.cropperOutputMime = file.type
      this.fileObject = file
    },
    async setInitialData() {
      this.isRequestOnGoing = true
      let response = await this.fetchCurrentUser()
      this.isFetchingData = true
      if (response.data.user) {
        this.formData.name = response.data.user.company.name
        this.formData.address_street_1 =
          response.data.user.company.address.address_street_1
        this.formData.address_street_2 =
          response.data.user.company.address.address_street_2
        this.formData.zip = response.data.user.company.address.zip
        this.formData.phone = response.data.user.company.address.phone
        this.formData.state = response.data.user.company.address.state
        this.formData.city = response.data.user.company.address.city
        this.formData.cif = response.data.user.company.address.cif
        this.country = response.data.user.company.address.country
        this.previewLogo = '/uploads/' + response.data.user.company.logo
      }
      this.isRequestOnGoing = false
    },
    async updateCompanyData() {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        return true
      }
      this.isLoading = true

      let response = await this.updateCompany(this.formData)
      if (response.data.success) {
        this.isLoading = false
        if (this.fileObject && this.previewLogo) {
          let logoData = new FormData()
          logoData.append(
            'company_logo',
            JSON.stringify({
              name: this.fileObject.name,
              data: this.previewLogo,
            })
          )
          await this.updateCompanyLogo(logoData)
        }
        this.isLoading = false
        window.toastr['success'](
          this.$t('settings.company_info.updated_message')
        )
        return true
      }
      this.isLoading = false
      window.toastr['error'](response.data.error)
      return true
    },
  },
}
</script>
