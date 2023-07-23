export default class AlertComponent {
  constructor() {
    this.alert = document.getElementById('app-alert');
  }

  /**
   * Affichage d'un message d'alerte en haut de l'écran
   * @param {string} type Type du message : error, warning, info, success
   * @param {string} message Message
   */
  displayAlert(type, message) {
    const className = `alert-${type}`;

    this.alert.classList.add(className);
    this.alert.innerText = message;

    // eslint-disable-next-line no-unused-vars
    setTimeout(() => {
      this.alert.classList.remove(className);
    }, 3000);
  }

  /**
   * Affichage d'un message de succès en haut de l'écran
   * @param {string} message Message
   */
  displaySuccessAlert(message) {
    this.displayAlert('success', message);
  }

  /**
   * Affichage d'un message d'erreur en haut de l'écran
   * @param {string} message Message
   */
  displayErrorAlert(message) {
    this.displayAlert('error', message);
  }

  /**
   * Affichage d'un message d'avertissement en haut de l'écran
   * @param {string} message Message
   */
  displayWarningAlert(message) {
    this.displayAlert('warning', message);
  }

  /**
   * Affichage d'un message d'information en haut de l'écran
   * @param {string} message Message
   */
  displayInfoAlert(message) {
    this.displayAlert('info', message);
  }
}
