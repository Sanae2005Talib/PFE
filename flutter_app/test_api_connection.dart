// Script de test pour vérifier la connexion API
// Exécuter avec: dart run test_api_connection.dart

import 'dart:convert';
import 'package:http/http.dart' as http;

const String baseUrl = 'http://app.solidarite.test/api';

void main() async {
  print('🔍 Test de connexion API...\n');
  
  // Test 1: Régions
  print('1️⃣ Test GET /regions/get_all.php');
  await testEndpoint('$baseUrl/regions/get_all.php');
  
  // Test 2: Types de dons
  print('\n2️⃣ Test GET /donation_types/get_all.php');
  await testEndpoint('$baseUrl/donation_types/get_all.php');
  
  // Test 3: Associations
  print('\n3️⃣ Test GET /associations/get_all.php');
  await testEndpoint('$baseUrl/associations/get_all.php');
  
  // Test 4: Détails association (ID 1)
  print('\n4️⃣ Test GET /associations/get_details.php?id=1');
  await testEndpoint('$baseUrl/associations/get_details.php?id=1');
  
  print('\n✅ Tests terminés!');
}

Future<void> testEndpoint(String url) async {
  try {
    final response = await http.get(Uri.parse(url));
    final data = json.decode(response.body);
    
    if (data['success'] == true) {
      print('   ✅ Succès - ${data['data']?.length ?? 0} résultat(s)');
      if (data['data'] != null && data['data'].isNotEmpty) {
        print('   📄 Premier élément: ${json.encode(data['data'][0])}');
      }
    } else {
      print('   ❌ Échec - ${data['message'] ?? 'Erreur inconnue'}');
    }
  } catch (e) {
    print('   ❌ Erreur: $e');
  }
}
