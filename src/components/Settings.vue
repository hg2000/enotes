<template>
	<div class="card card-1">
		<form @submit.prevent="submit">
			<div class="settings-group">
				<div class="group-title">
					<h3>Email accounts</h3>
					<div>
						Activate Email Acccounts which will be scanned for
						emails which contain ebook reader note attachments
					</div>
				</div>

				<div v-for="account in settings.mailAccounts"
					 class="form-check">
					<input
						class="checkbox"
						type="checkbox"
						name="accounts[]"
						v-model="account.active"
						:id="'account-' + account.id">
					<label :for="'account-' + account.id">
						{{ account.email }}
					</label>
				</div>
			</div>

			<div class="settings-group">
				<div class="group-title">
					<h3>Email sender addresses</h3>
					<div>
						Emails from this senders will be scanned (comma
						separated list)
					</div>
				</div>
				<div>
					<select name="type1">
						<option v-for="type in settings.types" :value="type.key">{{type.name}}</option>
					</select>

					<input
						class="settings-text"
						:value="settings.types[0].senders"
						type="text"
						name="type1-senders"
						id="senders"
						disabled>
				</div>

			</div>

			<div class="settings-group">
				<button @click="fetch"
						class="primary">
					Fetch notes from emails
				</button>
			</div>

			<div class="settings-group">
				<button @click="updateSettings"
						class="primary">
					Save
				</button>
			</div>
		</form>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import routes from '../routes'

export default {
	name: 'Settings',
	data () {
		return {
			settings: {
			}
		}
	},
	mounted () {
		this.get()
	},
	methods: {
		emitSuccessAlert (message) {
			this.$emit("showSuccessAlert", message);
		},

		emitErrorAlert (message) {
			this.$emit("showErrorAlert", message);
		},
		emitIsLoading() {
			this.$emit("isLoading")
		},
		emitStopLoading() {
			this.$emit("stopLoading")
		},

		get () {
			const vm = this
			vm.emitIsLoading()
			axios
				.get(routes.getSettings)
				.then(function (response) {
					vm.settings = JSON.parse(response.data)
					vm.emitStopLoading()
				})
				.catch(function (error) {
					vm.emitErrorAlert(error)
					vm.emitStopLoading()
				})
		},
		updateSettings () {
			const vm = this
			vm.emitIsLoading()
			axios
				.put(routes.updateSettings, vm.settings)
				.then(function (response) {

					vm.emitSuccessAlert('Settings saved.')
					vm.emitStopLoading()
				})
				.catch(function (error) {
					vm.emitErrorAlert(error)
					vm.emitStopLoading()
				})
		},

		fetch() {
			this.$emit("fetchNotes")
		}

	},
}
</script>
