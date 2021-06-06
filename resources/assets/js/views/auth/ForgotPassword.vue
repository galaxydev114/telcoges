<template>
  <form id="loginForm" @submit.prevent="validateBeforeSubmit">
    <div class="mb-4">
      <sw-input
        :invalid="$v.formData.email.$error"
        v-model.lazy="formData.email"
        :disabled="isSent"
        :placeholder="$t('login.enter_email')"
        focus
        name="email"
        @blur="$v.formData.email.$touch()"
      />
      <div v-if="$v.formData.email.$error">
        <span v-if="!$v.formData.email.required" class="text-sm text-danger">
          Se requiere campo
        </span>
        <span v-if="!$v.formData.email.email" class="text-sm text-danger">
          Email incorrecto.
        </span>
      </div>
    </div>
    <sw-button
      :loading="isLoading"
      :disabled="isLoading"
      type="submit"
      variant="primary"
    >
      <div v-if="!isSent">Enviar enlace de restablecimiento</div>
      <div v-else>¿Aún no? Envíalo de nuevo</div>
    </sw-button>

    <div class="mt-4 mb-4 text-sm">
      <router-link
        to="/login"
        class="text-sm text-primary-400 hover:text-gray-700"
      >
        ¿Volver al inicio de sesión?
      </router-link>
    </div>
  </form>
</template>

<script type="text/babel">
import { async } from 'q'
const { required, email } = require('vuelidate/lib/validators')

export default {
  data() {
    return {
      formData: {
        email: '',
      },
      isSent: false,
      isLoading: false,
    }
  },
  validations: {
    formData: {
      email: {
        email,
        required,
      },
    },
  },
  methods: {
    async validateBeforeSubmit(e) {
      this.$v.formData.$touch()
      if (!this.$v.formData.$invalid) {
        try {
          this.isLoading = true
          let res = await axios.post(
            '/api/v1/auth/password/email',
            this.formData
          )

          if (res.data) {
            toastr['success']('El correo enviado con éxito!', 'Éxito')
          }

          this.isSent = true
          this.isLoading = false
        } catch (err) {
          this.isLoading = false
        }
      }
    },
  },
}
</script>
