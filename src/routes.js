import { generateUrl } from '@nextcloud/router'

const  routes = {
	'getNotes': generateUrl('apps/enotes/note'),
	'getFetch': generateUrl('apps/enotes/fetch'),
	'getSettings': generateUrl('apps/enotes/settings'),
	'updateSettings': generateUrl('apps/enotes/settings')
}

export default routes
