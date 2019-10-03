<template>
  <div id="app">

    <div class="header">
      <div class="logo"></div>
      <div class="title">
        <h2>Installation</h2>
        <h1>SchuleIntern</h1>
      </div>
    </div>
    

    <Server v-if="steps == 'server'" v-bind:apiRoot="apiRoot" ></Server>

    <Settings v-if="steps == 'settings'" v-bind:apiRoot="apiRoot" ></Settings>

    <Finish v-if="steps == 'finish'" ></Finish>

  </div>
</template>

<script>

import axios from "axios";

import Server from "./components/Server.vue";
import Settings from "./components/Settings.vue";
import Finish from "./components/Finish.vue";


export default {
  name: 'app',
  components: {
    Server,
    Settings,
    Finish
  },
  data: function () {
    return {
      apiRoot: '../',
      //dataInit: false,
      dataPost: {},
      steps: 'server'
    }
  },
  created: function () {
    //this.init();

    EventBus.$on('done--step', data => {
      console.log(data)

      if (data.server == true) {
        console.log('server done!');
        this.steps = 'settings';
      }

      if (data.settings == true && data.values) {
        console.log('settings done!');
        console.log(data.values);
        this.steps = 'finish';
      }


    });

  },  
  methods: {
    // init: function () {

    //   var that = this;

    //   axios.get(this.apiRoot+'setup.php?action=server')
    //   .then( function(response) {
    //     that.setDataInit(response.data);
    //   })

    // },
    // setDataInit: function (data) {

    //   this.dataInit = data;
    //   this.presettings();

    // },
    done: function (data) {
      console.log(data);
    },
    presettings: function () {

      this.dataPost.uri = this.dataInit.urlToIndex;
      this.dataPost.cronkey = this.dataInit.cronkey;
      this.dataPost.apikey = this.dataInit.apikey;
      this.dataPost.adminpass = this.dataInit.adminpass;

      this.dataPost.dbport = 3306;
      this.dataPost.adminuser = 'admin';
      
    },
    install: function (event) {

      axios.get(this.apiRoot+'setup.php')
      .then( function(response) {
        console.log(response.data);
      })


    }
  }
}
</script>

<style>

.header {
  display: flex;
  height: 10vh;
  margin-top: 3vh;
  margin-bottom: 5vh;
}

.header .logo {
  flex: 1;
  max-width: 8vw;
  background-image: url('assets/logo.png');
  background-repeat: no-repeat;
  background-size: contain;
}
.header .title {
  flex: 1;
  padding-left: 2vw;
  display: flex;
  flex-direction: column;
}
</style>
