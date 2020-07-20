<template>
  <q-page class="flex-center flex">
    <q-card square>
      <q-card-section class="bg-deep-purple-7 q-pa-sm">
        <div class="text-h5 text-white">Login</div>
      </q-card-section>
      <q-card-section class="q-pa-lg">
        <q-form
          v-on:submit.prevent="onSubmit()"
          class="q-px-sm q-pt-md q-pb-lg"
        >
          <q-input
            square
            ref="username"
            v-model="form.username"
            :label="$t('auth.login_fields.username')"
            @keypress.enter="$refs.password.focus()"
            autofocus
          >
            <template v-slot:prepend>
              <q-icon name="person" />
            </template>
          </q-input>

          <q-input
            square
            ref="password"
            v-model="form.password"
            :type="isPwd ? 'password' : 'text'"
            :label="$t('auth.login_fields.password')"
            @keypress.enter="onSubmit"
          >
            <template v-slot:prepend>
              <q-icon name="lock" />
            </template>
            <template v-slot:append>
              <q-icon
                :name="isPwd ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="isPwd = !isPwd"
              />
            </template>
          </q-input>
        </q-form>

        <transition
          appear
          enter-active-class="animated bounceIn"
          leave-active-class="animated fadeOut"
        >
          <q-banner
            class="text-white bg-red text-center"
            dense
            rounded
            v-if="error"
          >
            {{ error }}
          </q-banner>
        </transition>
      </q-card-section>
      <q-card-actions class="q-px-lg">
        <q-btn
          @click.prevent="onSubmit()"
          unelevated
          size="lg"
          color="purple-4"
          class="full-width text-white"
          label="Login"
          :loading="loading"
        />
      </q-card-actions>
      <q-card-section class="text-center q-pa-sm">
        <p class="text-grey-6">
          Don't have an account?
          <router-link to="/register">Register.</router-link>
        </p>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: "PageLogin",
  data() {
    return {
      isPwd: true,
      form: {
        username: "",
        password: ""
      },
      error: "",
      loading: false
    };
  },
  methods: {
    onSubmit() {
      this.error = "";
      this.loading = true;
      this.$store
        .dispatch("auth/login", {
          credentials: this.form
        })
        .then(data => {
          let url = this.$router.currentRoute.query["redirect"] || "/";
          this.$router.push(decodeURIComponent(url));
        })
        .catch(data => {
          this.error = data.error
            ? this.$t("auth.failure." + data.error)
            : this.$t("auth.failure.UNKNOWN");
          this.form.password = "";
          this.$refs.password.$el.focus();
        })
        .finally(() => {
          this.loading = false;
        });
    }
  },
  preFetch({ store }) {
    if (store.getters["auth/isLoggedIn"]) {
      return store.dispatch("auth/logout");
    }
  }
};
</script>