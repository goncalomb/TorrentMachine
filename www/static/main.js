(function() {

  function apiFetch(url) {
    return fetch(url).then(response => {
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

  Vue.component('torrent-list', {
    props: ['torrents', 'error'],
    template: `
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
          <td class="text-danger" colspan="5">{{ error }}</td>
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
    `
  });

  window.createTorrentList = function(el) {
    new Vue({
      el: el,
      template: '<torrent-list :torrents="torrents" :error="error"></torrent-list>',
      data: {
        torrents: [],
        error: null
      },
      methods: {
        update: function() {
          apiFetch('/api/list.php').then(data => {
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
  }

})();
