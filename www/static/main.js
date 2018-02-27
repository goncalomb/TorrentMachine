(function() {

  var globalMediaPlayer = null;

  function apiFetch(url, data) {
    var init = {};
    if (data) {
      init = {
        method: 'POST',
        body: $.param(data),
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        }
      }
    }
    return fetch(url, init).then(response => {
      if (response.status >= 200 && response.status < 300) {
        return response.json();
      } else {
        return Promise.reject(new Error(response.status + ' ' + response.statusText));
      }
    }).then(json => {
      if (json.error) {
        return Promise.reject(new Error(json.error));
      } else {
        return Promise.resolve(json.data);
      }
    });
  }

  var MediaPlayer = Vue.extend({
    name: 'media-player',
    template: `
    <div v-if="src" class="card bg-light mb-3">
      <p class="p-3 m-0">
        <strong style="line-height: 1.9rem;">{{ name }}</strong>
        <span class="float-right">
          <a v-show="srcSubtitles === null" class="btn btn-sm btn-outline-secondary" href="javascript:void(0);" @click="searchSubtitles"><i class="fa fa-comment-o"></i> Search for English Subtitles</a>
          <a class="btn btn-sm btn-secondary" href="javascript:void(0);" @click="src = null"><i class="fa fa-close"></i> Close Player</a>
        </span>
      </p>
      <video v-if="src" width="100%" :src="src" controls autoplay>
        <track v-if="srcSubtitles" label="English" kind="subtitles" srclang="en" :src="srcSubtitles" default>
      </video>
    </div>
    `,
    data: function() {
      return {
        name: "",
        src: null,
        srcSubtitles: null
      };
    },
    methods: {
      playerAvailable: function(path) {
        var i = path.lastIndexOf('.');
        if (i != -1) {
          var ext = path.substr(i);
          return (ext == '.avi' || ext == '.mp4' || ext == '.mkv')
        }
        return false;
      },
      playerOpen: function(path) {
        this.name = path.split('/').pop();
        this.src = path;
        this.srcSubtitles = null;
        setTimeout(() => {
          this.$el.scrollIntoView();
          window.scrollBy(0, -10);
        }, 10);
      },
      searchSubtitles: function() {
        this.srcSubtitles = "/subtitles/?name=" + window.encodeURIComponent(this.name);
      }
    }
  })

  var DirTree = Vue.extend({
    name: 'dir-tree',
    props: ['path', 'name'],
    template: `
    <div>
      <a v-show="entries === null" href="javascript:void(0);" @click="load" title="open"><i class="fa fa-fw fa-chevron-right"></i></a>
      <a v-show="entries !== null" href="javascript:void(0);" @click="entries = null" title="close"><i class="fa fa-fw fa-chevron-down"></i></a>
      <a :href="'/files/' + path">{{ name }}/</a>
      <ul style="list-style: none;">
        <li v-for="entry in entries">
          <dir-tree v-if="entry.type == 'dir'" :path="entry.path" :name="entry.name"></dir-tree>
          <a v-if="entry.type == 'file' && playerAvailable(entry.path)" href="javascript:void(0);" @click="playerOpen(entry.path)" title="open on media player"><i class="fa fa-fw fa-film"></i></a>
          <a v-if="entry.type == 'file'" :href="'/files/' + entry.path" target="_blank">{{ entry.name }}</a>
        </li>
      </ul>
    </div>
    `,
    data: function() {
      return {
        entries: null
      };
    },
    methods: {
      load: function() {
        apiFetch('/api/filesystem/?action=list', {
          path: this.path
        }).then(entries => {
          entries.forEach(entry => {
            entry.path = this.path + entry.name;
            if (entry.type == 'dir') {
              entry.path += '/';
            }
          });
          this.entries = entries;
        }).catch(error => {
          alert(error.message);
        });
      },
      playerAvailable: function(path) {
        if (globalMediaPlayer) {
          return globalMediaPlayer.playerAvailable('/files/' + path);
        }
        return false;
      },
      playerOpen: function(path) {
        if (globalMediaPlayer) {
          globalMediaPlayer.playerOpen('/files/' + path);
        }
      }
    }
  });

  var TorrentAddForm = Vue.extend({
    name: 'torrent-add-form',
    template: `
    <form @submit="submit">
      <div :class="['alert', { 'alert-success': !messageIsError }, { 'alert-danger': messageIsError }]" v-show="message">{{ message }}</div>
      <div class="input-group input-group-lg mb-3">
        <input class="form-control" type="text" placeholder="Torrent URL / Magnet URL / Info Hash" v-model="url" :disabled="locked">
        <div class="input-group-append">
          <button class="btn btn-outline-primary" type="submit" :disabled="locked">Add</button>
        </div>
      </div>
    </form>
    `,
    data: function() {
      return {
        message: null,
        messageIsError: false,
        messageTimeout: 0,
        locked: false,
        url: ''
      };
    },
    methods: {
      setMessage: function(text, isError) {
        clearTimeout(this.messageTimeout);
        this.message = text;
        this.messageIsError = isError;
        if (text && !isError) {
          this.messageTimeout = setTimeout(() => {
            this.message = '';
          }, 2500);
        }
      },
      submit: function(e) {
        e.preventDefault();
        if (this.url) {
          this.locked = true;
          apiFetch('/api/transmission/?action=torrent-add', {
            url: this.url
          }).then(data => {
            this.locked = false;
            this.url = '';
            this.setMessage(data, false);
          }).catch(error => {
            this.locked = false;
            this.setMessage(error.message, true);
          });
        }
        document.activeElement.blur();
      }
    }
  });

  var TorrentList = Vue.extend({
    name: 'torrent-list',
    template: `
    <div style="overflow-x: auto;">
      <table class="table table-sm">
        <thead>
          <tr>
            <th>Name</th>
            <th>Size</th>
            <th>Downloaded</th>
            <th>Progress</th>
            <th>ETA</th>
            <th>Status</th>
            <th>Peers (U/D)</th>
            <th>Download</th>
            <th>Upload</th>
          </tr>
        </thead>
        <tbody>
          <tr v-show="error">
            <td class="text-danger" colspan="9">{{ error }}</td>
          </tr>
          <tr v-for="torrent in torrents">
            <td>{{ torrent.name }}</td>
            <td>{{ torrent.totalSize }}</td>
            <td>{{ torrent.haveValid }}</td>
            <td>{{ torrent.totalSize ? Math.floor(torrent.haveValid*10000/torrent.totalSize)/100 : 0 }} %</td>
            <td>{{ torrent.eta > 0 ? torrent.eta : 0 }}</td>
            <td>{{ torrent.statusString }}</td>
            <td>{{ torrent.peersConnected + '/' + torrent.maxConnectedPeers + ' (' + torrent.peersGettingFromUs + '/' + torrent.peersSendingToUs + ')' }}</td>
            <td>{{ torrent.rateDownload }}</td>
            <td>{{ torrent.rateUpload }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    `,
    data: function() {
      return {
        torrents: [],
        error: null
      };
    },
    methods: {
      update: function() {
        apiFetch('/api/transmission/?action=torrent-get').then(data => {
          this.torrents = data;
          this.error = null;
        }).catch(error => {
          this.torrents = [];
          this.error = error.message;
        });
      }
    },
    mounted: function() {
      this.update();
      setInterval(() => {
        this.update();
      }, 5000);
    }
  });

  window.createMediaPlayer = function(el) {
    return globalMediaPlayer = new MediaPlayer({ el });
  }

  window.createDownloadsListing = function(el) {
  return new Vue({
      el: el,
      template: `
      <div style="overflow-x: auto;">
        <dir-tree path="downloads/" name="downloads"></dir-tree>
      </div>
      `,
      components: { DirTree }
    });
  };

  window.createTorrentAddForm = function(el) {
    return new TorrentAddForm({ el });
  };

  window.createTorrentList = function(el) {
    return new TorrentList({ el });
  };

})();
