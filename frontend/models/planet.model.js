export default class PlanetModel {
  constructor(planet) {
    this.id = 0;
    this.name = '';
    this.position = 0;
    this.ownerId = 0;

    this.fromArray(planet);
  }

  fromArray(planet) {
    if (planet) {
      this.id = planet.id ?? this.id;
      this.name = planet.name ?? this.name;
      this.position = planet.position ?? this.position;
      this.ownerId = planet.ownerId ?? this.ownerId;
    }
  }
}
