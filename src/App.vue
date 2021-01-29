<template>
	<Content :class="{'icon-loading': isLoading}" app-name="enotes">
		<AppNavigation>
			<template id="app-enotes-navigation" #list>
				<AppNavigationItem v-for="book in books"
								   :key="book.id"
								   :title="book.title"
								   :allow-collapse="false"
								   icon="icon-folder"
								   @click="selectBook(book),selectView('note')"
				/>

				<AppNavigationItem title="Settings"
								   icon="icon-settings-dark"
								   :pinned="true"
								   @click="selectView('settings')"
				/>
			</template>
		</AppNavigation>
		<AppContent>
			<div v-if="alertMessage" :class="alertClass" @click="clear">
				{{ alertMessage }}
				<button type="button" class="close" data-dismiss="alert"
						aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div id="container">
				<div v-if="showLoadingIcon" class="icon-loading spinner"/>
				<Settings
					v-if="view.settings"
					@showSuccessAlert="showSuccessAlert($event)"
					@isLoading="displayLoadingIcon"
					@stopLoading="hideLoadingIcon"
					@fetchNotes="fetchNotes"
				/>
				<Note v-if="view.notes" v-for="note in book.notes"
					  :note="note"
					  :book="book"/>

			</div>
		</AppContent>
	</Content>
</template>

<script>
import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import axios from '@nextcloud/axios'
import AppSidebar from '@nextcloud/vue/dist/Components/AppSidebar'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppContentList from '@nextcloud/vue/dist/Components/AppContentList'
import Note from './components/Note'
import Settings from './components/Settings'
import routes from './routes'

export default {
	name: 'App',
	components: {
		Content,
		AppContent,
		AppSidebar,
		AppNavigation,
		AppNavigationItem,
		AppContentList,
		Note,
		Settings
	},
	data () {
		return {
			view: {
				notes: true,
				settings: false
			},
			showLoadingIcon: false,
			show: false,
			starred: false,
			settings: {'email': 'Lorem  ipsums'},
			isSettingsPage: false,
			alertMessage: '',
			alertType: '',
			note: {
				type: '',
				content: '',
				location: '',
			},
			book: {
				id: 0,
				title: '',
				notes: [],
			},
			books: [],
		}
	},

	mounted () {
		this.getNotes()
	},
	methods: {
		handleError (error) {
			if (error.response) {
				// Request made and server responded
				this.alertMessage = error.response.data
				this.alertType = 'error'
			} else if (error.request) {
				// The request was made but no response was received
				console.log(error.request)
			} else {
				// Something happened in setting up the request that triggered an Error
				console.log('Error', error.message)
			}
		},
		hiderAlert() {
			this.alertMessage = null
		},
		showSuccessAlert (message) {
			this.alertMessage = message
			this.alertType = "success"
			this.hideLoadingIcon()
		},
		showErrorAlert (message) {
			this.alertMessage = message
			this.alertType = "error"
			this.hideLoadingIcon()
		},
		displayLoadingIcon () {
			this.showLoadingIcon = true
		},
		hideLoadingIcon () {
			this.showLoadingIcon = false
		},
		selectView (view) {
			switch (view) {
				case 'settings':
					this.view.notes = !this.view.notes
					this.view.settings = !this.view.settings
					break
				default:
					this.view.notes = true
					this.view.settings = false
			}
			this.hiderAlert()
		},
		getNotes () {
			const vm = this
			vm.displayLoadingIcon()
			axios
				.get(routes.getNotes)
				.then(function (response) {
					vm.books = JSON.parse(response.data)
					if (typeof vm.books !== 'undefined' && vm.books.length > 0) {
						vm.selectBook(vm.books[0])
					}
					vm.hideLoadingIcon()
				})
				.catch(function (error) {
					vm.handleError(error)
				})
		},
		fetchNotes () {
			const vm = this
			vm.displayLoadingIcon()
			axios
				.get(routes.getFetch)
				.then(function (response) {
					vm.showSuccessAlert("Fetched notes")
				})
				.catch(function (error) {
					vm.handleError(error)
				})
		},

		selectNote (note) {
			this.note = note
			this.book = this.books.filter(function (book) {
				return (book.id === note.bookId)
			}).pop()
		},

		updateSettings () {
			const vm = this
			axios
				.put(routes.updateSettings, vm.settings)
				.then(function (response) {
					vm.settings = JSON.parse(response.data)
				})
				.catch(function (error) {
					vm.handleError(error)
				})
		},
		selectBook (book) {
			this.book = book
		},
		clear () {
			this.alertMessage = '';
		},
	},

	computed: {
		alertClass: function () {
			if (this.alertMessage && this.alertType === "error") {
				return "alert alert-error"
			}
			return "alert alert-success"
		}
	}
}
</script>
