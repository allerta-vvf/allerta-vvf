import L from 'leaflet';
import 'leaflet.locatecontrol';
import '../node_modules/leaflet.locatecontrol/dist/L.Control.Locate.min.css'
import '../node_modules/leaflet/dist/leaflet.css';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'resources/dist/marker-icon-2x.png',
  iconUrl: 'resources/dist/marker-icon.png',
  shadowUrl: 'resources/dist/marker-shadow.png',
});