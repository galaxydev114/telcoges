<template>
  <form
    id="registerForm"
    style="margin-top: -2rem"
    @submit.prevent="validateBeforeSubmit"
  >
    <sw-input-group
      :label="$t('signup.name')"
      :error="nameError"
      class="mb-4"
      required
    >
      <sw-input
        :invalid="$v.signupData.name.$error"
        :placeholder="$t('signup.enter_name')"
        v-model="signupData.name"
        focus
        type="text"
        name="name"
        @input="$v.signupData.name.$touch()"
      />
    </sw-input-group>
    <sw-input-group
      :label="$t('signup.email')"
      :error="emailError"
      class="mb-4"
      required
    >
      <sw-input
        :invalid="$v.signupData.email.$error"
        :placeholder="$t('signup.enter_email')"
        v-model="signupData.email"
        focus
        type="email"
        name="email"
        @input="$v.signupData.email.$touch()"
      />
    </sw-input-group>
    <sw-input-group
      :label="$t('signup.password')"
      :error="passwordError"
      class="mb-4"
      required
    >
      <sw-input
        v-model="signupData.password"
        :invalid="$v.signupData.password.$error"
        :placeholder="$t('signup.enter_password')"
        :type="getInputType"
        name="password"
        @input="$v.signupData.password.$touch()"
      >
        <template v-slot:rightIcon>
          <eye-off-icon
            v-if="isShowPassword"
            class="w-5 h-5 mr-1 text-gray-500 cursor-pointer"
            @click="isShowPassword = !isShowPassword"
          />
          <eye-icon
            v-else
            class="w-5 h-5 mr-1 text-gray-500 cursor-pointer"
            @click="isShowPassword = !isShowPassword"
          />
        </template>
      </sw-input>
    </sw-input-group>
    <sw-input-group
      :label="$t('signup.retype_password')"
      :error="retypePasswordError"
      class="mb-4"
      required
    >
      <sw-input
        :placeholder="$t('signup.retype_password')"
        v-model="signupData.password_confirmation"
        :invalid="$v.signupData.password_confirmation.$error"
        :type="getInputType"
        name="password_confirmation"
        @input="$v.signupData.password_confirmation.$touch()"
      />
    </sw-input-group>
    <sw-button
      :loading="isLoading"
      :disabled="isLoading"
      type="submit"
      variant="primary"
      class="w-100 text-uppercase"
    >
      {{ $t('signup.create_account') }}
    </sw-button>
    <div class="mt-5 pt-3 text-center top-hr">
      <span class="text-sm">{{ $t('signup.already_has_account') }}</span>
      <router-link
        to="login"
        class="text-sm text-primary-400 hover:text-gray-700"
      >
        {{ $t('signup.login') }}
      </router-link>
    </div>
  </form>
</template>
<script type="text/babel">
import { mapActions } from 'vuex'
import { EyeIcon, EyeOffIcon } from '@vue-hero-icons/outline'
const {
  required,
  email,
  sameAs,
  minLength,
} = require('vuelidate/lib/validators')

export default {
  components: {
    EyeIcon,
    EyeOffIcon,
  },
  data() {
    return {
      signupData: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
      },
      submitted: false,
      isLoading: false,
      isShowPassword: false,
    }
  },
  validations: {
    signupData: {
      name: {
        required,
      },
      email: {
        required,
        email,
      },
      password: {
        required,
        minLength: minLength(8),
      },
      password_confirmation: {
        sameAsPassword: sameAs('password'),
      },
    },
  },

  computed: {
    nameError() {
      if (!this.$v.signupData.name.$error) {
        return ''
      }
      if (!this.$v.signupData.name.required) {
        return this.$tc('validation.required')
      }
    },

    emailError() {
      if (!this.$v.signupData.email.$error) {
        return ''
      }
      if (!this.$v.signupData.email.required) {
        return this.$tc('validation.required')
      }
      if (!this.$v.signupData.email.email) {
        return this.$tc('validation.email_incorrect')
      }
    },

    passwordError() {
      if (!this.$v.signupData.password.$error) {
        return ''
      }
      if (!this.$v.signupData.email.required) {
        return this.$tc('validation.required')
      }
      if (!this.$v.signupData.password.minLength) {
        return this.$tc(
          'validation.password_min_length',
          this.$v.signupData.password.$params.minLength.min,
          { count: this.$v.signupData.password.$params.minLength.min }
        )
      }
    },

    retypePasswordError() {
      if (!this.$v.signupData.password_confirmation.$error) {
        return ''
      }

      if (!this.$v.signupData.password_confirmation.sameAsPassword) {
        return this.$tc('validation.password_incorrect')
      }
    },

    getInputType() {
      if (this.isShowPassword) {
        return 'text'
      }
      return 'password'
    },
  },

  watch: {
    'signupData.password'(val) {
      if (!val) {
        this.signupData.password_confirmation = ''
      }
    },
  },

  methods: {
    ...mapActions('auth', ['register']),
    async validateBeforeSubmit() {
      this.$v.signupData.$touch()
      if (this.$v.$invalid) {
        return true
      }

      this.isLoading = true

      try {
        await this.register(this.signupData).then((res) => {
          if (res.data.success) {
            this.$router.push('/login')
          }
        })
        this.isLoading = false
      } catch (error) {
        this.isLoading = false
      }
    },
  },
}
</script>
