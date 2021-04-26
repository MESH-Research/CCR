<template>
  <div>
    <h2 class="q-pl-lg">
      Publications
    </h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Create New Publication</h3>
        <q-form
          @submit="createPublication()"
        >
          <q-input
            v-model="new_publication.name"
            outlined
            class="q-mb-lg"
            label="Enter Name"
          />
          <q-btn
            :disabled="is_submitting"
            class="bg-primary text-white"
            type="submit"
          >
            Save
          </q-btn>
        </q-form>
      </section>
      <section class="col-md-7 col-sm-6 col-xs-12">
        <h3>All Publications</h3>
        <ol class="scroll">
          <q-item
            v-for="publication in publications.data"
            :key="publication.id"
          >
            <li>
              {{ publication.name }}
            </li>
          </q-item>
        </ol>
        <span v-if="publications.data.length == 0">
          No Publications Created
        </span>
      </section>
    </div>
  </div>
</template>

<script>
import { GET_PUBLICATIONS } from 'src/graphql/queries';
import { CREATE_PUBLICATION } from 'src/graphql/mutations';
import { validationMixin } from 'vuelidate';
import { required, maxLength } from 'vuelidate/lib/validators';

export default {
  mixins: [validationMixin],
  data() {
    return {
      is_submitting: false,
      publications: {
        data: []
      },
      new_publication: {
        name: ""
      }
    }
  },
  validations: {
    new_publication: {
      name: {
        required,
        maxLength: maxLength(256)
      }
    }
  },
  apollo: {
    publications: {
      query: GET_PUBLICATIONS
    }
  },
  methods: {
    notify(color, icon, message) {
      this.$q.notify({
        color: color,
        message: this.$t(message),
        icon: icon,
        attrs: {
          'data-cy': 'create_publication_notify'
        },
        html: true
      });
      this.is_submitting = false
    },
    async createPublication() {
      this.is_submitting = true
      this.$v.$touch();
      if (!this.$v.new_publication.name.maxLength) {
        this.notify("negative","error","publications.create.max_length")
        return false;
      }
      if (!this.$v.new_publication.name.required) {
        this.notify("negative","error","publications.create.required")
        return false;
      }
      try {
        await this.$apollo.mutate({
          mutation: CREATE_PUBLICATION,
          variables: this.new_publication,
        })
        this.notify("positive","check_circle","publications.create.success")
        this.publications.data.push({name:this.new_publication.name});
        this.new_publication.name = "";
      } catch (error) {
        this.notify("negative","error","publications.create.failure")
      }
    }
  },
}
</script>