<template>
  <div class="settings">
    
    <h3>Install</h3>
    
    <h4>Dies kann einige Zeit in Anspruch nehmen. Bitte haben Sie Geduld.</h4>

    <div class="outer-box">
      <div class="box">
        <ul>

          <li>
            <div class="box-long">
              Daten vom Server laden und entpacken
              <div class="text-red">{{errors.downloadBranch.errorMsg}}</div>
            </div>
            <div class="box-icon">
              <img v-if="!loading && errors.downloadBranch && !errors.downloadBranch.errorMsg" src="../assets/icons/check.svg" alt="ok" title="ok"/>
              <img v-if="!loading && (errors.downloadBranch==false || errors.downloadBranch.errorMsg)" src="../assets/icons/cancel.svg" alt="error" title="error"/>
              <img v-if="loading" src="../assets/icons/spinner.gif" alt="loading" title="loading"/>
            </div>
          </li>
          <li>
            <div class="box-long">
              Webserver Basisverzeichnis befüllen (CSS, JS, Bilder)
              <div class="text-red">{{errors.moveFiles.errorMsg}}</div>
            </div>
            <div class="box-icon">
              <img v-if="!loading && errors.moveFiles && !errors.moveFiles.errorMsg" src="../assets/icons/check.svg" alt="ok" title="ok"/>
              <img v-if="!loading && (errors.moveFiles==false || errors.moveFiles.errorMsg)" src="../assets/icons/cancel.svg" alt="error" title="error"/>
              <img v-if="loading" src="../assets/icons/spinner.gif" alt="loading" title="loading"/>
            </div>
          </li>
          <li>
            <div class="box-long">
              Konfigurationsdatei schreiben (data/config/config.php)
              <div class="text-red">{{errors.makeConfig.errorMsg}}</div>
            </div>
            <div class="box-icon">
              <img v-if="!loading && errors.makeConfig && !errors.makeConfig.errorMsg" src="../assets/icons/check.svg" alt="ok" title="ok"/>
              <img v-if="!loading && (errors.makeConfig==false || errors.makeConfig.errorMsg)" src="../assets/icons/cancel.svg" alt="error" title="error"/>
              <img v-if="loading" src="../assets/icons/spinner.gif" alt="loading" title="loading"/>
            </div>
          </li>
          <li>
            <div class="box-long">
              Mit der Datenbank verbinden und Tabellen erstellen
              <div class="text-red">{{errors.initDbTable.errorMsg}}</div>
            </div>
            <div class="box-icon">
              <img v-if="!loading && errors.initDbTable && !errors.initDbTable.errorMsg" src="../assets/icons/check.svg" alt="ok" title="ok"/>
              <img v-if="!loading && (errors.initDbTable==false || errors.initDbTable.errorMsg)" src="../assets/icons/cancel.svg" alt="error" title="error"/>
              <img v-if="loading" src="../assets/icons/spinner.gif" alt="loading" title="loading"/>
            </div>
          </li>
          <li>
            <div class="box-long">
              Administratorzugang anlegen
              <div class="text-red">{{errors.dbPreSettings.errorMsg}}</div>
            </div>
            <div class="box-icon">
              <img v-if="!loading && errors.dbPreSettings && !errors.dbPreSettings.errorMsg" src="../assets/icons/check.svg" alt="ok" title="ok"/>
              <img v-if="!loading && (errors.dbPreSettings==false || errors.dbPreSettings.errorMsg)" src="../assets/icons/cancel.svg" alt="error" title="error"/>
              <img v-if="loading" src="../assets/icons/spinner.gif" alt="loading" title="loading"/>
            </div>
          </li>
          <li>
            <div class="box-long">
              Temporäre daten löschen
              <div class="text-red">{{errors.removeFolder.errorMsg}}</div>
            </div>
            <div class="box-icon">
              <img v-if="!loading && errors.removeFolder && !errors.removeFolder.errorMsg" src="../assets/icons/check.svg" alt="ok" title="ok"/>
              <img v-if="!loading && (errors.removeFolder==false || errors.removeFolder.errorMsg)" src="../assets/icons/cancel.svg" alt="error" title="error"/>
              <img v-if="loading" src="../assets/icons/spinner.gif" alt="loading" title="loading"/>
            </div>
          </li>

          
          
        </ul>
        
        <button v-if="!loading && install == false" class="btn" @click="back">Zurück</button>

      </div>
    
      <div v-if="install" class="box" >
        Hat alles geklapt!

        <button v-if="!loading" class="btn" @click="back">Zurück</button>


      </div>
    
    </div>
    
  </div>
</template>

<script>

import axios from "axios";

export default {
  name: 'Finish',
  props: {
    apiRoot: String,
    userValues: Object
  },
  data: function () {
    return {
      loading: false,
      install: false,
      errors: {
        downloadBranch: {},
        moveFiles: {},
        makeConfig: {},
        initDbTable: {},
        dbPreSettings: {},
        removeFolder: {}
      }
    }
  },
  created: function () {

    console.log('intall...');
    
    this.loading = true;

    var that = this;
    var params = new URLSearchParams();
    for (var prop in this.userValues) {
      params.append(prop, this.userValues[prop]);
    }
    axios.post(this.apiRoot+'setup.php?action=install', params)
    .then( function(response) {
      if ( response.data.install == true ) {
        console.log('DONE!');
        that.loading = false;
        that.install = true;
      } else {

        if (response.data && response.data.list) {
          that.errors = response.data.list;
        }

        console.log('error', that.errors);
        that.loading = false;
      }
    }).catch(function (error) {
      console.error(error);
      that.loading = false;
    });
    
    
  },
  methods: {
    
    back: function () {

      EventBus.$emit('done--step', {
        server: true
      })

    }

  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
