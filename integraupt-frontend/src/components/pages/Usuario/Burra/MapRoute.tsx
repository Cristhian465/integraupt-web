import React, { useEffect } from 'react';
import { MapContainer, TileLayer, Polyline, Marker, Popup, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import routeA from './data/routeA.json';
import routeB from './data/routeB.json';

// Fix Leaflet's default icon path issues with React
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

interface MapRouteProps {
  routeType: 'A' | 'B';
}

// Removed hardcoded coords in favor of JSON routes

const RecenterAutomatically = ({ center }: { center: [number, number] }) => {
  const map = useMap();
  useEffect(() => {
    map.setView(center, map.getZoom());
  }, [center, map]);
  return null;
};

export const MapRoute: React.FC<MapRouteProps> = ({ routeType }) => {
  const isRouteB = routeType === 'B';
  const center: [number, number] = isRouteB ? [-18.0110, -70.2420] : [-18.0080, -70.2390];

  const idaCoords = (isRouteB ? routeB : routeA) as [number, number][];
  const colorIda = isRouteB ? '#3b82f6' : '#8b5cf6';
  const dropOffPoint: [number, number] = isRouteB ? [-18.0377, -70.2505] : [-18.0105, -70.2458];

  return (
    <div style={{ height: '100%', width: '100%', borderRadius: '12px', overflow: 'hidden', border: '1px solid #e2e8f0' }}>
      <MapContainer 
        center={center} 
        zoom={14} 
        style={{ height: '100%', width: '100%' }}
        scrollWheelZoom={true}
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        
        <RecenterAutomatically center={center} />

        {/* Ruta Circuito Exacto (Calles) */}
        <Polyline positions={idaCoords} color={colorIda} weight={6} opacity={0.8} dashArray="10, 10">
          <Popup>Ruta de Servicio</Popup>
        </Polyline>

        {/* Markers for endpoints */}
        <Marker position={idaCoords[0]}>
          <Popup>UPT - Campus Capanique (Paradero Inicial)</Popup>
        </Marker>
        <Marker position={dropOffPoint}>
          <Popup>{isRouteB ? 'Cono Sur - Eduardo Pérez Gamboa (Recojo/Baja)' : 'Plaza Zela - Av. San Martín (Paradero de Bajada)'}</Popup>
        </Marker>
      </MapContainer>
    </div>
  );
};
