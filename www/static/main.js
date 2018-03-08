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

  function formatSize(b) {
    if (b >= 1073741824) {
      return Math.floor(b/10737418.24)/100 + ' GB';
    } else if (b >= 1048576) {
      return Math.floor(b/10485.76)/100 + ' MB';
    } else if (b >= 1024) {
      return Math.floor(b/10.24)/100 + ' KB';
    } else if (b == 1) {
      return b + ' byte';
    }
    return b + ' bytes';
  }

  function formatDuration(t) {
    if (!t) return '0s';
    return (t < 0 ? '-' : '') + ((t) => {
      var d = Math.floor(t/86400);
      var h = Math.floor(t%86400/3600);
      var m = Math.floor(t%3600/60);
      var s = Math.floor(t%60);
      return (d ? d + 'd' : '') + (h ? h + 'h' : '') + (m ? m + 'm' : '') + (s ? s + 's' : '');
    })(Math.abs(t));
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
      <i v-show="entries === false" class="fa fa-fw fa-circle-o-notch fa-spin"></i>
      <a v-show="entries" href="javascript:void(0);" @click="entries = null" title="close"><i class="fa fa-fw fa-chevron-down"></i></a>
      <a :href="'/files/' + path" target="_blank">{{ name }}/</a>
      <ul style="list-style: none; margin-left: 9px; padding-left: 8px; border-left: 2px solid #ddd;">
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
        this.entries = false;
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
    <div>
      <div class="form-inline mb-2">
        <div class="btn-group mb-2 mr-sm-2">
          <button class="btn btn-outline-secondary" @click="torrentAction('start')" title="start all torrents"><i class="fa fa-play"></i> Start All</button>
          <button class="btn btn-outline-secondary" @click="torrentAction('stop')" title="pause all torrents"><i class="fa fa-pause"></i> Pause All</button>
        </div>
        <div class="input-group mb-2 mr-sm-2">
          <div class="input-group-prepend">
            <div class="input-group-text">Show:</div>
          </div>
          <select class="form-control" v-model="filterStatus">
            <option value="-1" default>All ({{ torrents.length }})</option>
            <option value="0">Stopped ({{ countByStatus[0] }})</option>
            <option value="1">Verifying ({{ countByStatus[1] }})</option>
            <option value="2">Downloading ({{ countByStatus[2] }})</option>
            <option value="3">Seeding ({{ countByStatus[3] }})</option>
          </select>
        </div>
      </div>
      <div style="overflow-x: auto;">
        <table class="table table-sm">
          <thead>
            <tr>
              <th></th>
              <th>Torrent</th>
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
              <td class="text-danger" colspan="8">{{ error }}</td>
            </tr>
            <tr v-for="torrent in torrents" v-if="filterStatus == -1 || torrent.statusSimple == filterStatus">
              <td>
                <div class="btn-group btn-group-sm">
                  <button v-if="!torrent.status" class="btn btn-outline-secondary" @click="torrentAction('start', torrent.id)" title="start"><i class="fa fa-fw fa-play"></i></button>
                  <button v-if="torrent.status" class="btn btn-outline-secondary" @click="torrentAction('stop', torrent.id)" title="pause"><i class="fa fa-fw fa-pause"></i></button>
                  <button class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="more options"></button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="javascript:void(0);" @click="torrentAction('start', torrent.id)"><i class="fa fa-fw fa-play"></i> Start</a>
                    <a class="dropdown-item" href="javascript:void(0);" @click="torrentAction('stop', torrent.id)"><i class="fa fa-fw fa-pause"></i> Pause</a>
                    <a class="dropdown-item" href="javascript:void(0);" @click="torrentAction('verify', torrent.id)"><i class="fa fa-fw fa-check"></i> Verify</a>
                    <a class="dropdown-item" href="javascript:void(0);" @click="torrentAction('reannounce', torrent.id)"><i class="fa fa-fw fa-users"></i> Ask for more peers</a>
                    <a class="dropdown-item" href="javascript:void(0);" @click="if (confirm('Remove torrent and delete data?')) torrentAction('remove', torrent.id)"><i class="fa fa-fw fa-trash"></i> Remove</a>
                  </div>
                </div>
              </td>
              <td>
                {{ torrent.name }}<br>
                <small>{{ torrent.haveValid | formatSize }} of {{ torrent.totalSize | formatSize }}</small>
              </td>
              <td v-if="torrent.recheckProgress">{{ Math.floor(torrent.recheckProgress*10000)/100 }}%</small>
              <td v-else>{{ torrent.totalSize ? Math.floor(torrent.haveValid*10000/torrent.totalSize)/100 : 0 }}%</td>
              <td v-if="torrent.eta >= 0">{{ torrent.eta | formatDuration }}</td><td v-else></td>
              <td>{{ torrent.statusString }}</td>
              <td>{{ torrent.peersConnected + '/' + torrent.maxConnectedPeers + ' (' + torrent.peersGettingFromUs + '/' + torrent.peersSendingToUs + ')' }}</td>
              <td v-if="torrent.rateDownload">{{ torrent.rateDownload | formatSize }}/s</td><td v-else></td>
              <td v-if="torrent.rateUpload">{{ torrent.rateUpload | formatSize }}/s</td><td v-else></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    `,
    data: function() {
      return {
        filterStatus: -1,
        countByStatus: { 0: 0, 1: 0, 2: 0, 3: 0 },
        torrents: [],
        error: null
      };
    },
    methods: {
      update: function() {
        return apiFetch('/api/transmission/?action=torrent-get').then(data => {
          this.countByStatus = { 0: 0, 1: 0, 2: 0, 3: 0 };
          data.forEach((t) => {
            t.statusSimple = (t.status == 0 ? 0 : Math.ceil(t.status/2));
            this.countByStatus[t.statusSimple]++;
          });
          this.torrents = data;
          this.error = null;
        }).catch(error => {
          this.torrents = [];
          this.error = error.message;
        });
      },
      torrentAction: function(action, ids) {
        if (!ids) {
          ids = this.torrents.map(t => t.id).join(',');
        }
        apiFetch('/api/transmission/?action=torrent-' + action, {
          ids: ids
        }).then(data => {
          this.update();
        }).catch(error => {
          alert(error.message);
        });
        document.activeElement.blur();
      }
    },
    filters: {
      formatSize, formatDuration
    },
    mounted: function() {
      var continuousUpdate = () => {
        this.update().then(() => {
          setTimeout(continuousUpdate, 5000);
        });
      }
      continuousUpdate();
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
        <dir-tree path="" name="" ref="tree"></dir-tree>
      </div>
      `,
      components: { DirTree },
      mounted: function() {
        this.$refs.tree.load()
      }
    });
  };

  window.createTorrentAddForm = function(el) {
    return new TorrentAddForm({ el });
  };

  window.createTorrentList = function(el) {
    return new TorrentList({ el });
  };

})();
