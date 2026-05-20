import 'package:http/http.dart' as http;
import 'dart:convert';

class AssociationService {
  // Remplacez par votre IP locale (ex: 10.0.2.2 pour l'émulateur Android)
  final String url = "http://app.solidarite.test/api/get_associations.php";

  Future<List> fetchAssociations() async {
    final response = await http.get(Uri.parse(url));

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Erreur lors du chargement des données');
    }
  }
} 