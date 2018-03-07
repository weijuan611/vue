<template>
   <section>
      <headbar></headbar>
      <sidebar></sidebar>
      <centerContent></centerContent>
    </section>
</template>

<script>
  import headbar from './../../components/frame/Header.vue'
  import sidebar from './../../components/frame/Sidebar.vue'
  import centerContent from './../../components/frame/Content.vue'

  export default {
    components: {
      headbar, sidebar, centerContent
    }
  }
</script>
