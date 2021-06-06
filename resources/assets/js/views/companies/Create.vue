<template>
  <base-page v-if="isSuperAdmin" class="company-create">
    <form action="" @submit.prevent="submitCompany">
      <sw-page-header :title="pageTitle" class="mb-3">
        <sw-breadcrumb slot="breadcrumbs">
          <sw-breadcrumb-item
            :title="$t('general.home')"
            to="/admin/dashboard"
          />
          <sw-breadcrumb-item
            :title="$tc('companies.company', 2)"
            to="/admin/companies"
          />
          <sw-breadcrumb-item
            v-if="$route.name === 'companies.edit'"
            :title="$t('companies.edit_company')"
            to="#"
            active
          />
          <sw-breadcrumb-item
            v-else
            :title="$t('companies.new_company')"
            to="#"
            active
          />
        </sw-breadcrumb>
        <template slot="actions">
          <sw-button
            :loading="isLoading"
            :disabled="isLoading"
            variant="primary"
            type="submit"
            size="lg"
            class="hidden md:relative md:flex"
          >
            <save-icon v-if="!isLoading" class="mr-2 -ml-1" />

            {{
              isEdit
                ? $t('companies.update_company')
                : $t('companies.save_company')
            }}
          </sw-button>
        </template>
      </sw-page-header>

      <sw-card variant="company-card">
        <!-- Company Info -->
        <div class="grid grid-cols-5 gap-4 mb-8">
          <h6 class="col-span-5 sw-section-title lg:col-span-1">
            {{ $tc('companies.company_info.company_info') }}
          </h6>

          <div
            class="grid col-span-5 lg:col-span-4 gap-y-6 gap-x-4 md:grid-cols-6"
          >
            <sw-input-group
              :label="$tc('companies.company_info.name')"
              :error="companyNameError"
              class="md:col-span-3"
              required
            >
              <sw-input
                :invalid="$v.formData.companyName.$error"
                v-model.trim="formData.companyName"
                focus
                type="text"
                name="companyName"
                @input="$v.formData.companyName.$touch()"
              />
            </sw-input-group>
            <sw-input-group
              :label="$tc('companies.company_info.phone')"
              class="md:col-span-3"
            >
              <sw-input
                v-model.trim="formData.companyPhone"
                type="text"
                name="companyPhone"
              />
            </sw-input-group>
            <sw-input-group
              :label="$tc('companies.company_info.state')"
              class="md:col-span-3"
            >
              <sw-input v-model="formData.state" name="state" type="text" />
            </sw-input-group>
            <sw-input-group
              :label="$tc('companies.company_info.city')"
              class="md:col-span-3"
            >
              <sw-input v-model="formData.city" name="city" type="text" />
            </sw-input-group>
            <sw-input-group
              :label="$tc('companies.company_info.zip')"
              class="md:col-span-3"
            >
              <sw-input v-model="formData.zip" />
            </sw-input-group>
            <sw-input-group
              :label="$tc('companies.company_info.cif')"
              :error="cifError"
              class="md:col-span-3"
              required
            >
              <sw-input
                :invalid="$v.formData.cif.$error"
                :placeholder="$tc('companies.company_info.cif_placeholder')"
                v-model.trim="formData.cif"
                type="text"
                name="cif"
              />
            </sw-input-group>
            <div class="md:col-span-3">
              <sw-input-group
                :label="$tc('companies.company_info.address')"
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

              <sw-input-group :error="address2Error">
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
        </div>

        <sw-divider class="mb-5 md:mb-8" />

        <!-- Billing Address -->
        <div class="grid grid-cols-5 gap-4 mb-8">
          <h6 class="col-span-5 sw-section-title lg:col-span-1">
            {{ $tc('companies.administrator_info.administrator_info') }}
          </h6>

          <div
            class="grid col-span-5 lg:col-span-4 gap-y-6 gap-x-4 md:grid-cols-6"
          >
            <sw-input-group
              :label="$tc('companies.administrator_info.name')"
              :error="nameError"
              class="md:col-span-3"
              required
            >
              <sw-input
                v-model.trim="formData.name"
                :invalid="$v.formData.name.$error"
                :placeholder="$tc('companies.administrator_info.name_placeholder')"
                type="text"
                name="name"
                @input="$v.formData.name.$touch()"
              />
            </sw-input-group>
            <sw-input-group
              :label="$tc('companies.administrator_info.email')"
              :error="emailError"
              class="md:col-span-3"
              required
            >
              <sw-input
                :invalid="$v.formData.email.$error"
                :placeholder="$tc('companies.administrator_info.email_placeholder')"
                v-model.trim="formData.email"
                type="text"
                name="email"
                @input="$v.formData.email.$touch()"
              />
            </sw-input-group>
            <sw-input-group
              v-show="!isEdit"
              :label="$tc('companies.administrator_info.password')"
              :error="passwordError"
              required
              class="md:col-span-3"
            >
              <sw-input
                v-model="formData.password"
                :invalid="$v.formData.password.$error"
                :placeholder="$tc('companies.administrator_info.password_placeholder')"
                type="password"
                @input="$v.formData.password.$touch()"
              />
            </sw-input-group>
          </div>
        </div>
        <!-- Mobile Submit Button  -->
        <sw-button
          :disabled="isLoading"
          :loading="isLoading"
          variant="primary"
          type="submit"
          size="lg"
          class="flex w-full sm:hidden md:hidden"
        >
          <save-icon v-if="!isLoading" class="mr-2 -ml-1" />
          {{
            isEdit
              ? $t('companies.update_company')
              : $t('companies.save_company')
          }}
        </sw-button>
      </sw-card>
    </form>
  </base-page>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
const {
  required,
  minLength,
  email,
  numeric,
  minValue,
  maxLength,
  requiredIf,
} = require('vuelidate/lib/validators')

export default {
  data() {
    return {
      isLoading: false,
      title: 'Guardar empresa',

      formData: {
        companyName: '',
        companyPhone: '',
        cif: '',
        zip: '',
        address_street_1: '',
        address_street_2: '',
        state: '',
        city: '',
        name: '',
        email: null,
        password: null,
      },
    }
  },

  computed: {
    ...mapGetters('user', ['currentUser']),
    isSuperAdmin() {
      return this.currentUser.role == 'super admin'
    },

    pageTitle() {
      if (this.$route.name === 'companies.edit') {
        return this.$t('companies.edit_company')
      }
      return this.$t('companies.new_company')
    },

    isEdit() {
      if (this.$route.name === 'companies.edit') {
        return true
      }
      return false
    },

    companyNameError() {
      if (!this.$v.formData.companyName.$error) {
        return ''
      }
      if (!this.$v.formData.companyName.required) {
        return this.$t('validation.required')
      }
    },
    cifError() {
      if (!this.$v.formData.cif.$error) {
        return ''
      }
      if (!this.$v.formData.cif.required) {
        return this.$t('validation.required')
      }
    },
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
    nameError() {
      if (!this.$v.formData.name.$error) {
        return ''
      }
      if (!this.$v.formData.name.required) {
        return this.$t('validation.required')
      }
      if (!this.$v.formData.name.minLength) {
        return this.$tc(
          'validation.name_min_length',
          this.$v.formData.name.$params.minLength.min,
          { count: this.$v.formData.name.$params.minLength.min }
        )
      }
    },

    emailError() {
      if (!this.$v.formData.email.$error) {
        return ''
      }

      if (!this.$v.formData.email.email) {
        return this.$tc('validation.email_incorrect')
      }

      if (!this.$v.formData.email.required) {
        return this.$tc('validation.required')
      }
    },

    passwordError() {
      if (!this.$v.formData.password.$error) {
        return ''
      }
      if (!this.$v.formData.password.required) {
        return this.$t('validation.required')
      }
      if (!this.$v.formData.password.minLength) {
        return this.$tc(
          'validation.password_min_length',
          this.$v.formData.password.$params.minLength.min,
          { count: this.$v.formData.password.$params.minLength.min }
        )
      }
    },
  },

  created() {
    if (!this.isSuperAdmin) {
      this.$router.push('/admin/dashboard')
    }
    if (this.isEdit) {
      this.loadEditData()
    }
  },

  mounted() {
    this.$v.formData.$reset()
  },
  validations: {
    formData: {
      companyName: {
        required,
      },
      name: {
        required,
        minLength: minLength(3),
      },
      email: {
        email,
        required,
      },
      cif: {
        required,
      },
      address_street_1: {
        maxLength: maxLength(255),
      },
      address_street_2: {
        maxLength: maxLength(255),
      },
      password: {
        required: requiredIf(function () {
          return !this.isEdit
        }),
        minLength: minLength(8),
      },
    },
  },

  methods: {
    ...mapActions('companies', ['addCompany', 'fetchCompany', 'updateCompany']),

    async loadEditData() {
      let response = await this.fetchCompany(this.$route.params.id)

      if (response.data) {
        this.formData = { ...this.formData, ...response.data.company }
      }
    },

    async submitCompany() {
      this.$v.formData.$touch()

      if (this.$v.$invalid) {
        return true
      }

      try {
        let response
        this.isLoading = true
        if (this.isEdit) {
          response = await this.updateCompany(this.formData)
          let data
          if (response.data.success) {
            window.toastr['success'](this.$tc('companies.updated_message'))
            this.$router.push('/admin/companies')
            this.isLoading = false
          }
          if (response.data.error) {
            window.toastr['error'](this.$t('validation.email_already_taken'))
          }
        } else {
          response = await this.addCompany(this.formData)
          let data
          if (response.data.success) {
            this.isLoading = false
            if (!this.isEdit) {
              window.toastr['success'](this.$tc('companies.created_message'))
              this.$router.push('/admin/companies')
              return true
            }
          }
        }
      } catch (err) {
        if (err.response.data.errors.email) {
          this.isLoading = false
        }
      }
    },
  },
}
</script>
