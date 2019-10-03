<template>
  <div class="settings">
    
    
    <form v-on:submit.prevent>
      <div class="btn-outer-box">
        <button @click="install" class="btn">Installieren</button>
      </div>
      
      <div class="outer-box">
        <div class="box">
          <h3 class="header-box green">Einstellungen</h3>
          <ul>
            <li class="box-input">
              Schulname
              <input type="text" v-model="values.name" placeholder="z.B. Staatliches Digitalgymnasium" required="true"/>
              <div class="input-underline"></div>
            </li>
            <li class="box-input">
              Schulnummer
              <input type="text" v-model="values.nummer" placeholder="0123" />
              <div class="input-underline">Vierstellig mit führender Null</div>
            </li>
            <li class="box-input">
              Name der Seite
              <input type="text" v-model="values.name1" placeholder="RSU" maxlength="10" minlength="2" class="" >
              <input type="text" v-model="values.name2" placeholder="intern" maxlength="10" minlength="2" class="" >
              <div class="input-underline">Zweiteilig. z.B. RSU intern</div>
            </li>
            <li class="box-input">
              URI zur Index.php
              <input type="text" v-model="values.uri" placeholder="https://" />
              <div class="input-underline">
                <strong>Beachten Sie bitte folgende Hinweise:</strong>
                <ul>
                  <li>Wenn Sie SSL verwenden (Empfohlen!), dann geben Sie hier bitte die URL mit https beginnend ein!</li>
                  <li>Stellen Sie bitte am Server die automatische Umleitung auf SSL aus! Dies übernimmt die Software für Sie.</li>
                </ul>
              </div>
            </li>
            <li class="box-input">
              Stundenplan Software
              <select v-model="values.stundenplan" class="" >
                  <option value="UNTIS">UNTIS</option>
                  <option value="SPM++">SPM++ / VPM++</option>
                  <option value="TIME2007">TIME2007</option>
                  <option value="WILLI">WILLI</option>
              </select>
            </li>
            <li class="box-input">
              Notenverwaltung aktivieren?
              <select v-model="values.notenverwaltung" class="">
                  <option value="0">Nein</option>
                  <option value="1">Ja</option>
              </select>
              <div class="input-underline">
                <strong>Bitte beachten:</strong>
                <br>Die Notenverwaltung ist bisher nur für Gymnasien und die Klassenstunden 5 bis 9 einsetzbar.
              </div>
            </li>
            <li class="box-input">
              Modus für Elternbenutzer
              <select v-model="values.elternbenutzer" class="" >
                  <option value="ASV_CODE">Registrierungscodes</option>
                  <option value="ASV_MAIL">E-Mailadresse aus ASV Import verwenden</option>
              </select>
          </li>
            
          </ul>

          <h3 class="header-box">System</h3>
          <ul>
            <li class="box-input">
              Schlüssel für Cron Jobs
              <input type="text" v-model="values.cronkey" placeholder=""  />
              <div class="input-underline">
                Mindestens 20 Stellen, max 30 Stellen
              </div>
            </li>
            <li class="box-input">
              Schlüssel für API
              <input type="text" v-model="values.apikey" placeholder=""  />
              <div class="input-underline">
                Mindestens 20 Stellen, max 30 Stellen
              </div>
            </li>
            <li class="box-input">
                Zu installierende Verson wählen 
                <select v-model="values.version" class="" >
                    <option value="stable">Stable - Stabile, geteste Versionen. Geeignet zum Produktivgebrauch.</option>
                    <option value="beta">Beta - Nicht getestete Beta Versionen. Nicht geeignet zum Produktivgebrauch.</option>
                </select>
                <div class="input-underline">
                  Ausgewählte Version wird vom Updateserver heruntergeladen.
                </div>
            </li>
          </ul>

        </div>

        <div class="box">
          <h3 class="header-box red">Datenbank</h3>
          <ul>
            <li class="box-input">
              <strong>Bitte beachten Sie, dass die Datenbank bereits angelegt sein muss.</strong>
            </li>
            <li class="box-input">
              Datenbank - Host
              <input type="text" v-model="values.dbhost" placeholder="z.B. localhost" />
            </li>
            <li class="box-input">
              Datenbank - Port
              <input type="text" v-model="values.dbport" placeholder="" />
            </li>
            <li class="box-input">
              Datenbank - Benutzername
              <input type="text" v-model="values.dbuser" placeholder="z.B. root" />
            </li>
            <li class="box-input">
              Datenbank - Passwort
              <input type="text" v-model="values.dbpass" placeholder="z.B. secret"/>
            </li>
            <li class="box-input">
              Datenbank - Datenbankname
              <input type="text" v-model="values.dbname" placeholder="z.B. schuleinterndatenbank" />
            </li>
          </ul>

          <h3 class="header-box blue">Administratorzugang</h3>

          <ul>
            <li class="box-input">
              Benutzername
              <input type="text" v-model="values.adminuser"  readonly/>
            </li>
            <li class="box-input">
              Passwort
              <input type="text" v-model="values.adminpass" />
              <div class="input-underline">
                Bitte merken!
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div class="btn-outer-box">
        <button @click="install" class="btn">Installieren</button>
      </div>
    </form>
    

  </div>
</template>

<script>

import axios from "axios";

export default {
  name: 'Settings',
  props: {
    apiRoot: String
  },
  data: function () {
    return {
      values: {}
    }
  },
  created: function () {
    
    this.init();
  
  },
  methods: {
    init: function () {

      var that = this;

      axios.get(this.apiRoot+'setup.php?action=settings')
      .then( function(response) {
        that.setData(response.data);
      }).catch(function (error) {
        console.error(error);
      });

    },
    setData: function (data) {

      this.values = data;

    },
    required: function () {

      if (!this.values.name
        || !this.values.nummer
        || !this.values.name1
        || !this.values.name2
        || !this.values.uri
        || !this.values.stundenplan
        || !this.values.notenverwaltung
        || !this.values.elternbenutzer
        || !this.values.version
        || !this.values.cronkey
        || !this.values.apikey
        || !this.values.dbhost
        || !this.values.dbport
        || !this.values.dbpass
        || !this.values.dbname
         ) {
          return false;
      } else {
          return true;
      }

    },
    install: function () {

      //TODO: auskommentieren
      // if ( !this.required() ) {
      //     return false;
      // }

      var that = this;

      var params = new URLSearchParams();

      for (var prop in this.values) {
        params.append(prop, this.values[prop]);
      }

      axios.post(this.apiRoot+'setup.php?action=install', params)
      .then( function(response) {

        if ( response.data.install == true ) {

          EventBus.$emit('done--step', {
            settings: true,
            values: that.values
          })

        } else {
          // TODO:
          console.log('error');
        }
        

        
      }).catch(function (error) {
        console.error(error);
      });


      

    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
