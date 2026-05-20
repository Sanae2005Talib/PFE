import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/association.dart';
import '../models/besoin.dart';
import '../models/region.dart';

/// Service pour communiquer avec l'API PHP
class ApiService {
  // URL de base de l'API - À MODIFIER selon votre configuration
  static const String baseUrl = 'http://app.solidarite.test/api';
  
  /// Login Admin/Association
  /// Retourne un Map avec 'success', 'message', 'user', 'token'
  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/login.php'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'email': email,
          'password': password,
        }),
      );
      
      final data = json.decode(response.body);
      return data;
    } catch (e) {
      print('Erreur login: $e');
      return {
        'success': false,
        'message': 'Erreur de connexion au serveur',
      };
    }
  }
  
  /// Récupérer toutes les associations
  /// Retourne une liste d'associations validées
  Future<List<Association>> getAssociations({
    int? regionId,
    String? search,
  }) async {
    try {
      // Construction de l'URL avec paramètres optionnels
      var uri = Uri.parse('$baseUrl/associations/get_all.php');
      
      Map<String, String> queryParams = {};
      if (regionId != null) queryParams['region_id'] = regionId.toString();
      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      
      if (queryParams.isNotEmpty) {
        uri = uri.replace(queryParameters: queryParams);
      }

      // Appel HTTP GET
      final response = await http.get(uri);
      final data = json.decode(response.body);

      // Vérifier si succès
      if (data['success'] == true) {
        List<Association> associations = [];
        for (var item in data['data']) {
          associations.add(Association.fromJson(item));
        }
        return associations;
      }
      return [];
    } catch (e) {
      print('Erreur getAssociations: $e');
      return [];
    }
  }

  /// Récupérer les détails d'une association + ses besoins
  /// Retourne un Map avec 'association', 'needs' et 'stats'
  Future<Map<String, dynamic>?> getAssociationDetails(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/associations/get_details.php?id=$id'),
      );
      final data = json.decode(response.body);

      if (data['success'] == true) {
        return data['data'];
      }
      return null;
    } catch (e) {
      print('Erreur getAssociationDetails: $e');
      return null;
    }
  }

  /// Récupérer les besoins d'une association
  /// Retourne une liste de besoins
  Future<List<Besoin>> getBesoins(int associationId) async {
    try {
      final details = await getAssociationDetails(associationId);
      
      if (details != null && details['needs'] != null) {
        List<Besoin> besoins = [];
        for (var item in details['needs']) {
          besoins.add(Besoin.fromJson(item));
        }
        return besoins;
      }
      return [];
    } catch (e) {
      print('Erreur getBesoins: $e');
      return [];
    }
  }

  /// Récupérer toutes les régions
  /// Pour les filtres
  Future<List<Region>> getRegions() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/regions/get_all.php'),
      );
      final data = json.decode(response.body);

      if (data['success'] == true) {
        List<Region> regions = [];
        for (var item in data['data']) {
          regions.add(Region.fromJson(item));
        }
        return regions;
      }
      return [];
    } catch (e) {
      print('Erreur getRegions: $e');
      return [];
    }
  }

  /// Récupérer tous les types de dons
  /// Pour les filtres
  Future<List<DonationType>> getDonationTypes() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/donation_types/get_all.php'),
      );
      final data = json.decode(response.body);

      if (data['success'] == true) {
        List<DonationType> types = [];
        for (var item in data['data']) {
          types.add(DonationType.fromJson(item));
        }
        return types;
      }
      return [];
    } catch (e) {
      print('Erreur getDonationTypes: $e');
      return [];
    }
  }
}
