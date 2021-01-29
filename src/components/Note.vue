<template>
	<div v-if="note.location" class="card card-3">
		<div class="note-content">
			{{ note.content }}
		</div>

		<div class="meta-wrap">
			<span class="meta-tag">{{ book.title }}</span>
			<span class="meta-tag">{{ note.location }}</span>
			<span class="meta-tag"> {{ note.type }}</span>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Note',
	props: {
		note: {
			type: Object,
			default() {
				return {}
			},
		},
		book: {
			type: Object,
			default() {
				return {}
			},
		},
	},
}
</script>
