export default class UserModel {
  constructor(user) {
    this.id = 0;
    this.name = '';
    this.mailAddress = '';

    this.fromArray(user);
  }

  fromArray(user) {
    if (user) {
      this.id = user.id ?? this.id;
      this.name = user.name ?? this.name;
      this.mailAddress = user.mail_address ?? this.mailAddress;
    }
  }
}
