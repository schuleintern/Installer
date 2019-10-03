<template>
  <div class="server">
    <p>
      <strong>Herzlich Willkommen bei der Installation zu SchuleIntern.</strong>
      <br>
      <br>
      Bitte legen Sie sich folgende Daten bereit:
    </p>
    <ul>
      <li>MySQL / MariaDB Datenbank mit passenden Zugangsdaten</li>
      <li>Mailaccount mit passenden Zugangsdaten</li>
    </ul>

    <div class="box">
        
      <h3>Server</h3>



      <ul>

        <li>
          <div class="box-long">
            Schreibrechte im Übergeordnetem Verzeichnis
            <p>
              SchuleIntern speichert alle Daten außerhalb des von außen erreichbaren Verzeichnisses.
            </p>
            <div class="info">
              {{values.upperDir}}
            </div>
          </div>
          <div class="box-icon">
            <img v-if="values.upperDirWriteable" src="../assets/icons/check.svg" alt="ok" title="ok"/>
            <img v-else-if="!values.upperDirWriteable" src="../assets/icons/cancel.svg" alt="error" title="error"/>
          </div>
        </li>

        <li>
          <div class="box-long">
            Schreibrechte im aktuellen Verzeichnis
            <div class="info">
              {{values.currentDir}}
            </div>
          </div>
          <div class="box-icon">
            <img v-if="values.currentDirWriteAble" src="../assets/icons/check.svg" alt="ok" title="ok"/>
            <img v-else-if="!values.currentDirWriteAble" src="../assets/icons/cancel.svg" alt="error" title="error"/>
          </div>
        </li>

        <li>
          <div class="box-long">
            PHP Version
            <div class="info">
              Min. 7.2 - Installiert: {{values.phpVersion}}
            </div>
          </div>
          <div class="box-icon">
            <img v-if="values.phpVersionCompare" src="../assets/icons/check.svg" alt="ok" title="ok"/>
            <img v-else-if="values.phpVersionCompare == false" src="../assets/icons/cancel.svg" alt="error" title="error"/>
          </div>
        </li>

      </ul>
      <div class="btn-box">
        <button v-if="btnState == false" @click="refresh"
          class="btn">Erneut Testen</button>
      </div>

    </div>

    <div class="btn-box">
      <button v-if="btnState" @click="next" class="btn">Weiter</button>
    </div>

    
  </div>
</template>

<script>

import axios from "axios";



export default {
  name: 'Server',
  props: {
    apiRoot: String
  },
  data: function () {
    return {
      values: false,
      btnState: false
    }
  },
  created: function () {
    this.init();
  },
  methods: {
    init: function () {

      var that = this;

      axios.get(this.apiRoot+'setup.php?action=server')
      .then( function(response) {
        that.setData(response.data);
      }).catch(function (error) {

        that.setData( {
          upperDirWriteable: false,
          currentDirWriteAble: false,
          phpVersionCompare: false
        });

        console.error(error);
      });

    },
    setData: function (data) {
console.log('setdata', data);
      this.values = data;

      if ( !this.required() ) {
          this.btnState = false;
      } else {
          this.btnState = true;
      }

    },
    required: function () {

      if (!this.values.upperDirWriteable
        || !this.values.currentDirWriteAble
        || !this.values.phpVersionCompare) {
          return false;
      } else {
          return true;
      }

    },
    refresh: function () {

      this.init();

    },
    next: function () {

      if ( !this.required() ) {
          return false;
      }

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
