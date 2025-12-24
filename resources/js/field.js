import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'
import PreviewField from './components/PreviewField'

Nova.booting((app, store) => {
  app.component('index-belongstomany', IndexField)
  app.component('detail-belongstomany', DetailField)
  app.component('form-belongstomany', FormField)
  // app.component('preview-belongstomany', PreviewField)
})
