using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
public class aidetect : MonoBehaviour {
	public dadut dda;
	public GameObject showgambarpajak;
	public GameObject[] hidejwb;
	public Button jwb1,jwb2;
	public prosessoal ambilsoal;
	private AudioSource langkah;
	public AudioClip langkahsound;
	public static double uangcomputer;
	public static double bank;
	public GameObject showflare;
	public PLAYER computer;
	public double rumah1 = 1000;
	public double rumah2 = 1200;
	public double rumah3 = 2000;
	public double r1k2 = 1000;
	public double r2k2 = 1000;
	public double r3k2 = 1000;
	public double r1k3 = 500;
	public double r2k3 = 1000;
	public double r3k3 = 1100;
	public double r1k4 = 600;
	public double r2k4 = 500;
	public double r3k4 = 1000;
	public double r1k5 = 900;
	public double r2k5 = 1000;
	public double r3k5 = 1250;
	public double r1k6 = 430;
	public double r2k6 = 570;
	public double r3k6 = 2000;
	public double r1k7 = 400;
	public double r2k7 = 500;
	public double r3k7 = 1500;
	public double r1k8 = 700;
	public double r2k8 = 800;
	public double r3k8 = 1000;
	public double r1k9 = 700;
	public double r2k9 = 1000;
	public double r3k9 = 1200;
	public double r1k10 = 800;
	public double r2k10 = 900;
	public double r3k10 = 1100;
	public double r1k11 = 1200;
	public double r2k11 = 1300;
	public double r3k11 = 1400;
	public double r1k12 = 300;
	public double r2k12 = 500;
	public double r3k12 = 780;
	public double r1k13 = 700;
	public double r2k13 = 850;
	public double r3k13 = 900;
	public double r1k16 = 1000;
	public double r2k16 = 1100;
	public double r3k16 = 1200;
	public double r1k17 = 1200;
	public double r2k17 = 1300;
	public double r3k17 = 1400;
	public double r1k18 = 1250;
	public double r2k18 = 1350;
	public double r3k18 = 1450;
	public double r1k20 = 1450;
	public double r2k20 = 1500;
	public double r3k20 = 1550;
	public double r1k21 = 1350;
	public double r2k21 = 1500;
	public double r3k21 = 1750;
	public double r1k22 = 1400;
	public double r2k22 = 1500;
	public double r3k22 = 1700;
	public double r1k24 = 1600;
	public double r2k24 = 1700;
	public double r3k24 = 1800;
	public double r1k27 = 1700;
	public double r2k27 = 1800;
	public double r3k27 = 1900;
	public double r1k28 = 1900;
	public double r2k28 = 2000;
	public double r3k28 = 2100;
	public GameObject[] rk1;
	public GameObject[] rk2;
	public GameObject[] rk3;
	public GameObject[] kotakpulaujawa;
	public GameObject[] rk5;
	public GameObject[] rk6;
	public GameObject[] soal1;
	public GameObject[] rk8;
	public GameObject[] rk9;
	public GameObject[] rk10;
	public GameObject[] kotakpulausumatra;
	public GameObject[] rk12;
	public GameObject[] rk13;
	public GameObject[] soal2;
	public GameObject[] rk16;
	public GameObject[] rk17;
	public GameObject[] rk18;
	public GameObject[] kotakpulausulewesi;
	public GameObject[] rk20;
	public GameObject[] rk21;
	public GameObject[] soal3;
	public GameObject[] rk22;
	public GameObject[] pajak;
	public GameObject[] rk24;
	public GameObject[] kotakpulaukalimantan;
	public GameObject[] soal4;
	public GameObject[] rk27;
	public GameObject[] rk28;
	public GameObject[] rk11;
	public double hargapulaujawa = 1100;
	public double hargapulaukalimantan = 1300;
	public double hargapulausumatra = 1200;
	public double hargapulausulewesi = 950;
	public GameObject penjara;
	public buildrumah br;
	public GameObject showpanelwin;

	public GameObject showsoal;
	// Use this for initialization
	void Start () {

	}
	void Awake(){
		langkah = GetComponent<AudioSource>();
	}
	// Update is called once per frame
	void Update () {
		if (uangcomputerbaru.uangcomputer < 0) {
			showpanelwin.SetActive (true);

			if (uangcomputerbaru.uangcomputer < 0) {
				uangcomputerbaru.uangcomputer = 0;
			}
		}
	}

	IEnumerator waktu_k1d1(){

		yield return new WaitForSeconds (1);
		rk1[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - rumah1;
		
	}
	IEnumerator waktu_k1d2(){

		yield return new WaitForSeconds (2);
		rk1[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - rumah2;
		
	}
	IEnumerator waktu_k1d3(){

		yield return new WaitForSeconds (3);
		rk1[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - rumah3;
		
	}
	//kotak2-----------------
	IEnumerator waktu_k2d1(){

		yield return new WaitForSeconds (1);
		rk2[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k2;
		
	}
	IEnumerator waktu_k2d2(){

		yield return new WaitForSeconds (2);
		rk2[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k2;
		
	}
	IEnumerator waktu_k2d3(){

		yield return new WaitForSeconds (3);
		rk2[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k2;
		
	}
	//kotak3------------------
	IEnumerator waktu_k3d1(){

		yield return new WaitForSeconds (1);
		rk3[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k3;
		
	}
	IEnumerator waktu_k3d2(){

		yield return new WaitForSeconds (2);
		rk3[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k3;
		
	}
	IEnumerator waktu_k3d3(){

		yield return new WaitForSeconds (3);
		rk3[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k3;
		
	}
	//kotakpulaujawa
	IEnumerator waktu_pulaujawa(){

		yield return new WaitForSeconds (1);
		kotakpulaujawa[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - hargapulaujawa;
		
	}
	//kotak5----------
	IEnumerator waktu_k5d1(){

		yield return new WaitForSeconds (1);
		rk5[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k5;
		
	}
	IEnumerator waktu_k5d2(){

		yield return new WaitForSeconds (2);
		rk5[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k5;
		
	}
	IEnumerator waktu_k5d3(){

		yield return new WaitForSeconds (3);
		rk5[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k5;
		
	}

	//kotak6-------------

	IEnumerator waktu_k6d1(){

		yield return new WaitForSeconds (1);
		rk6[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k6;
		
	}
	IEnumerator waktu_k6d2(){

		yield return new WaitForSeconds (2);
		rk6[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k6;
		
	}
	IEnumerator waktu_k6d3(){

		yield return new WaitForSeconds (3);
		rk6[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k6;
		
	}
	//soal1
	IEnumerator waktu_soal1(){

		yield return new WaitForSeconds (3);
		showsoal.SetActive (true);
		ambilsoal.jawabcom ();
	

	}
	IEnumerator hide_soal(){
		yield return new WaitForSeconds (4);
		hidejwb [0].SetActive (false);
		hidejwb [1].SetActive (false);
		showsoal.SetActive (false);
	
	}
	//kotak8
	IEnumerator waktu_k8d1(){

		yield return new WaitForSeconds (1);
		rk8[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k8;
		
	}
	IEnumerator waktu_k8d2(){

		yield return new WaitForSeconds (2);
		rk8[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k8;
		
	}
	IEnumerator waktu_k8d3(){

		yield return new WaitForSeconds (3);
		rk8[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k8;
		
	}
	//kotak9
	IEnumerator waktu_k9d1(){

		yield return new WaitForSeconds (1);
		rk9[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k9;
		
	}
	IEnumerator waktu_k9d2(){

		yield return new WaitForSeconds (2);
		rk9[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k9;
		
	}
	IEnumerator waktu_k9d3(){

		yield return new WaitForSeconds (3);
		rk9[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k9;
		
	}
	//kotak10
	IEnumerator waktu_k10d1(){

		yield return new WaitForSeconds (1);
		rk10[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k10;
		
	}
	IEnumerator waktu_k10d2(){

		yield return new WaitForSeconds (2);
		rk10[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k10;
		
	}
	IEnumerator waktu_k10d3(){

		yield return new WaitForSeconds (3);
		rk10[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k10;

	}
	//pulausumatra
	IEnumerator waktu_pulausumatra(){

		yield return new WaitForSeconds (2);
		kotakpulausumatra[0].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - hargapulausumatra;

	}
	//kotak12
	IEnumerator waktu_k12d1(){

		yield return new WaitForSeconds (1);
		rk12[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k12;
		
	}
	IEnumerator waktu_k12d2(){

		yield return new WaitForSeconds (2);
		rk12[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k12;

	}
	IEnumerator waktu_k12d3(){

		yield return new WaitForSeconds (3);
		rk12[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k12;

	}
	//kotak13
	IEnumerator waktu_k13d1(){

		yield return new WaitForSeconds (1);
		rk13[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k10;

	}
	IEnumerator waktu_k13d2(){

		yield return new WaitForSeconds (2);
		rk13[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k13;

	}
	IEnumerator waktu_k13d3(){

		yield return new WaitForSeconds (3);
		rk13[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k13;

	}
	//soal2
	IEnumerator waktu_soal2(){

		yield return new WaitForSeconds (3);
		showsoal.SetActive (true);
		ambilsoal.jawabcom ();


	}

	//kotak16
	IEnumerator waktu_k16d1(){

		yield return new WaitForSeconds (1);
		rk16[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k16;

	}
	IEnumerator waktu_k16d2(){

		yield return new WaitForSeconds (2);
		rk16[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k16;
	
	}
	IEnumerator waktu_k16d3(){

		yield return new WaitForSeconds (3);
		rk16[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k16;

	}
	//kotak17
	IEnumerator waktu_k17d1(){

		yield return new WaitForSeconds (1);
		rk17[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k17;
		
	}
	IEnumerator waktu_k17d2(){

		yield return new WaitForSeconds (2);
		rk17[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k17;
		
	}
	IEnumerator waktu_k17d3(){

		yield return new WaitForSeconds (3);
		rk17[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k17;
		
	}
	//kotak18
	IEnumerator waktu_k18d1(){

		yield return new WaitForSeconds (1);
		rk18[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k18;
		
	}
	IEnumerator waktu_k18d2(){

		yield return new WaitForSeconds (2);
		rk18[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k18;
		
	}
	IEnumerator waktu_k18d3(){

		yield return new WaitForSeconds (3);
		rk18[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k18;
		
	}
	//kotakpulausulewesi
	IEnumerator waktu_pulausulewesi(){

		yield return new WaitForSeconds (3);
		kotakpulausulewesi[0].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - hargapulausulewesi;
		
	}
	//kotak20
	IEnumerator waktu_k20d1(){

		yield return new WaitForSeconds (1);
		rk20[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k20;
		
	}
	IEnumerator waktu_k20d2(){

		yield return new WaitForSeconds (2);
		rk20[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k20;
		
	}
	IEnumerator waktu_k20d3(){

		yield return new WaitForSeconds (3);
		rk20[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k20;
		
	}
	//kotak21
	IEnumerator waktu_k21d1(){

		yield return new WaitForSeconds (1);
		rk21[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k21;
		
	}
	IEnumerator waktu_k21d2(){

		yield return new WaitForSeconds (2);
		rk21[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k21;
		
	}
	IEnumerator waktu_k21d3(){

		yield return new WaitForSeconds (3);
		rk21[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k21;
		
	}
	//soal3
	IEnumerator waktu_soal3(){

		yield return new WaitForSeconds (3);
		showsoal.SetActive (true);
		ambilsoal.jawabcom ();


	}

	//kotak22
	IEnumerator waktu_k22d1(){

		yield return new WaitForSeconds (1);
		rk22[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k22;
		
	}
	IEnumerator waktu_k22d2(){

		yield return new WaitForSeconds (2);
		rk22[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k22;
		
	}
	IEnumerator waktu_k22d3(){

		yield return new WaitForSeconds (3);
		rk22[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k22;
		
	}
	//pajak
	IEnumerator waktu_pajak(){

		yield return new WaitForSeconds (3);
		rk22[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k22;
		
	}
	//kotak24
	IEnumerator waktu_k24d1(){

		yield return new WaitForSeconds (1);
		rk24[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k24;
		
	}
	IEnumerator waktu_k24d2(){

		yield return new WaitForSeconds (2);
		rk24[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k24;
		
	}
	IEnumerator waktu_k24d3(){

		yield return new WaitForSeconds (3);
		rk24[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k24;
		
	}
	//pulaukalimantan
	IEnumerator waktu_pulaukalimantan(){

		yield return new WaitForSeconds (3);
		kotakpulaukalimantan[0].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - hargapulaukalimantan;
		
	}
	//soal4
	IEnumerator waktu_soal4(){

		yield return new WaitForSeconds (3);
		showsoal.SetActive (true);
		ambilsoal.jawabcom ();


	}

	//kotak27
	IEnumerator waktu_k27d1(){

		yield return new WaitForSeconds (1);
		rk27[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k27;
		
	}
	IEnumerator waktu_k27d2(){

		yield return new WaitForSeconds (2);
		rk27[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k27;
		
	}
	IEnumerator waktu_k27d3(){

		yield return new WaitForSeconds (3);
		rk27[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k27;
		
	}
	//kotak28
	IEnumerator waktu_k28d1(){

		yield return new WaitForSeconds (1);
		rk28[0].SetActive (true);

		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r1k28;
		
	}
	IEnumerator waktu_k28d2(){

		yield return new WaitForSeconds (2);
		rk28[1].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r2k28;
		
	}
	IEnumerator waktu_k28d3(){

		yield return new WaitForSeconds (3);
		rk28[2].SetActive (true);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - r3k28;
		
	}

	//detect bangunan milik sendiri





	IEnumerator delaybacktojail(){
		yield return new WaitForSeconds (4);
		penjara.SetActive (true);
		uangcomputerbaru.uangcomputer -= 500;

		uangbankk.uangbank += 500;
	}
	IEnumerator delaydetectjail(){
		yield return new WaitForSeconds (2);
		computer.blueplayerpos -= 8;
		computer.Counterpos++;
	}		
	public void kotakk6(){
		rk6 [0].SetActive (false);
		rk6 [1].SetActive (false);
		rk6 [2].SetActive (false);
		uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
	}
	IEnumerator delaygambarpajak(){
		yield return new WaitForSeconds (1);
		showgambarpajak.SetActive (true);
		yield return new WaitForSeconds (3);
		showgambarpajak.SetActive (false);
	}
	public void OnTriggerEnter(Collider collider){
		if (collider.gameObject.name == "1") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "2") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "3") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "pulaujawa") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "5") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "6") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "7") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "soal1") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "penjara") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "8") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "9") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "10") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "11") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "12") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "13") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "soal2") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "backtojail") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "16") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "17") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "18") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "pulausulewesi") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "20") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "21") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "soal3") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "bonusuang") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "22") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "pajak") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "24") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "pulaukalimantan") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "soal4") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "27") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "28") {
			langkah.PlayOneShot (langkahsound);
		}
		if (collider.gameObject.name == "0") {
			langkah.PlayOneShot (langkahsound);
		}
		//bonustart
		if (collider.gameObject.name == "0") {
			uangcomputerbaru.uangcomputer += 1750;
			uangbankk.uangbank -= 1750;



		}
	if (computer.Counterpos == computer.targetpos) {
			if (collider.gameObject.name == "soal1") {
				ambilsoal.Tampilsoal (Random.Range (0, 30));

				StartCoroutine (waktu_soal1 ());
				StartCoroutine (hide_soal ());
			}
			if (collider.gameObject.name == "soal2") {
				ambilsoal.Tampilsoal (Random.Range (0, 30));

				StartCoroutine (waktu_soal2 ());
				StartCoroutine (hide_soal ());
			}
			if (collider.gameObject.name == "soal3") {
				ambilsoal.Tampilsoal (Random.Range (0, 30));

				StartCoroutine (waktu_soal3 ());
				StartCoroutine (hide_soal ());
			}
			if (collider.gameObject.name == "soal4") {
				ambilsoal.Tampilsoal (Random.Range (0, 30));

				StartCoroutine (waktu_soal4 ());
				StartCoroutine (hide_soal ());
			}
		
			if (collider.gameObject.name == "pajak") {
				StartCoroutine (delaygambarpajak ());
				uangcomputerbaru.uangcomputer -= 750;

				uangbankk.uangbank += 750;
			}
			if (collider.gameObject.name == "bonusuang") {
				
				uangcomputerbaru.uangcomputer += 1200;

				uangbankk.uangbank -= 1200;
			}
			if (collider.gameObject.name == "penjara") {

			uangcomputerbaru.uangcomputer -= 500;
		
			uangbankk.uangbank += 500;
				penjara.SetActive (true);
		}
	

			//backtojail
			if (collider.gameObject.name == "backtojail") {
				StartCoroutine (delaydetectjail ());
				StartCoroutine (delaybacktojail ());
			}

			//kotak1
			if (collider.gameObject.name == "1") {

				if (br.rk1[0] == true) {
					rk1 [0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 200;
					uangplayerbaru.uangplayer += 200;


				}
				else
					if (br.rk1 [0].activeSelf == true) { 
						rk1 [0].SetActive (false);
						rk1 [1].SetActive (false);
						rk1 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk1 [0].activeSelf == false && br.rk1 [0].activeSelf == false) { 
							if (br.rk1 [1].activeSelf == true && br.rk1 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk1 [0].SetActive (false);
								rk1 [1].SetActive (false);
								rk1 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > rumah1) {
									StartCoroutine (waktu_k1d1 ());
								}

						}


				//--rumah2

				if (br.rk1 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 200;
					uangplayerbaru.uangplayer += 200;
					rk1 [1].SetActive (false);
				}
				if (br.rk1 [1].activeSelf == true) { 
					rk1 [0].SetActive (false);
					rk1 [1].SetActive (false);
					rk1 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk1 [1].activeSelf == false && br.rk1 [1].activeSelf == false) { 
						if (br.rk1 [2].activeSelf == true && br.rk1 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk1 [0].SetActive (false);
							rk1 [1].SetActive (false);
							rk1 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > rumah1) {
								StartCoroutine (waktu_k1d2 ());
							}
					}


				//--rumah3

				if (br.rk1 [2].activeSelf == true) {
					rk1 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 200;
					uangplayerbaru.uangplayer += 200;
				}
				if (br.rk1 [2].activeSelf == true) { 
					rk1 [0].SetActive (false);
					rk1 [1].SetActive (false);
					rk1 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk1 [2].activeSelf == false && br.rk1 [2].activeSelf == false) { 
						if(br.rk1 [1].activeSelf == true && br.rk1 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk1 [0].SetActive (false);
							rk1 [1].SetActive (false);
							rk1 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > rumah3)
								StartCoroutine (waktu_k1d3 ());

					}



			}
		//kotak2
			if (collider.gameObject.name == "2") {

				if (br.rk2 [0].activeSelf == true) {
					rk2 [0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 250;
					uangplayerbaru.uangplayer += 250;


				}
				else
					if (br.rk2 [0].activeSelf == true) { 
						rk2 [0].SetActive (false);
						rk2 [1].SetActive (false);
						rk2 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk2 [0].activeSelf == false && br.rk2 [0].activeSelf == false) { 
							if (br.rk2 [1].activeSelf == true && br.rk2 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk2 [0].SetActive (false);
								rk2 [1].SetActive (false);
								rk2 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k2) {
									StartCoroutine (waktu_k2d1 ());
								}

						}


				//--rumah2

				if (br.rk2 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 250;
					uangplayerbaru.uangplayer += 250;
					rk2 [1].SetActive (false);
				}
				if (br.rk2 [1].activeSelf == true) { 
					rk2 [0].SetActive (false);
					rk2 [1].SetActive (false);
					rk2 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk2 [1].activeSelf == false && br.rk2 [1].activeSelf == false) { 
						if (br.rk2 [2].activeSelf == true && br.rk2 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk2 [0].SetActive (false);
							rk2 [1].SetActive (false);
							rk2 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k2) {
								StartCoroutine (waktu_k2d2 ());
							}
					}


				//--rumah3

				if (br.rk2 [2].activeSelf == true) {
					rk2 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 250;
					uangplayerbaru.uangplayer += 250;
				}
				if (br.rk2 [2].activeSelf == true) { 
					rk2 [0].SetActive (false);
					rk2 [1].SetActive (false);
					rk2 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk2 [2].activeSelf == false && br.rk2 [2].activeSelf == false) { 
						if(br.rk2 [1].activeSelf == true && br.rk2 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk2 [0].SetActive (false);
							rk2 [1].SetActive (false);
							rk2 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k2)
								StartCoroutine (waktu_k2d3 ());

					}



			}
			//kotak3------------------
			if (collider.gameObject.name == "3") {

				if (br.rk3 [0].activeSelf == true) {
					rk3 [0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 300;
					uangplayerbaru.uangplayer += 300;


				}
				else
					if (br.rk3 [0].activeSelf == true) { 
						rk3 [0].SetActive (false);
						rk3 [1].SetActive (false);
						rk3 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk3 [0].activeSelf == false && br.rk3 [0].activeSelf == false) { 
							if (br.rk3 [1].activeSelf == true && br.rk3 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk3 [0].SetActive (false);
								rk3 [1].SetActive (false);
								rk3 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k3) {
									StartCoroutine (waktu_k3d1 ());
								}

						}


				//--rumah2

				if (br.rk3 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 300;
					uangplayerbaru.uangplayer += 300;
					rk3 [1].SetActive (false);
				}
				if (br.rk3 [1].activeSelf == true) { 
					rk3 [0].SetActive (false);
					rk3 [1].SetActive (false);
					rk3 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk3 [1].activeSelf == false && br.rk3 [1].activeSelf == false) { 
						if (br.rk3 [2].activeSelf == true && br.rk3 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk3 [0].SetActive (false);
							rk3 [1].SetActive (false);
							rk3 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k3) {
								StartCoroutine (waktu_k3d2 ());
							}
					}


				//--rumah3

				if (br.rk3 [2].activeSelf == true) {
					rk3 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 300;
					uangplayerbaru.uangplayer += 300;
				}
				if (br.rk3 [2].activeSelf == true) { 
					rk3 [0].SetActive (false);
					rk3 [1].SetActive (false);
					rk3 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk3 [2].activeSelf == false && br.rk3 [2].activeSelf == false) { 
						if(br.rk3 [1].activeSelf == true && br.rk3 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk3 [0].SetActive (false);
							rk3 [1].SetActive (false);
							rk3 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k3)
								StartCoroutine (waktu_k3d3 ());

					}



			}

			//kotakpulaujawa---------------
			if (collider.gameObject.name == "pulaujawa") {
				if (br.kotakpulaujawa [0].activeSelf == true) {
					uangcomputerbaru.uangcomputer -= 450;
					uangplayerbaru.uangplayer += 450;
				}
				else
					if (kotakpulaujawa[0].activeSelf==true) {
						
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
				}
					else
				StartCoroutine (waktu_pulaujawa());

			}	
			//kotak5---------------
			if (collider.gameObject.name == "5") {

				if (br.rk5 [0].activeSelf == true) {
					rk5 [0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 350;
					uangplayerbaru.uangplayer += 350;


				}
				else
					if (br.rk5 [0].activeSelf == true) { 
						rk5 [0].SetActive (false);
						rk5 [1].SetActive (false);
						rk5 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk5 [0].activeSelf == false && br.rk5 [0].activeSelf == false) { 
							if (br.rk5 [1].activeSelf == true && br.rk5 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk5 [0].SetActive (false);
								rk5 [1].SetActive (false);
								rk5 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k5) {
									StartCoroutine (waktu_k5d1 ());
								}

						}


				//--rumah2

				if (br.rk5 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 350;
					uangplayerbaru.uangplayer += 350;
					rk5 [1].SetActive (false);
				}
				if (br.rk5 [1].activeSelf == true) { 
					rk5 [0].SetActive (false);
					rk5 [1].SetActive (false);
					rk5 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk5 [1].activeSelf == false && br.rk5 [1].activeSelf == false) { 
						if (br.rk5 [2].activeSelf == true && br.rk5 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk5 [0].SetActive (false);
							rk5 [1].SetActive (false);
							rk5 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k5) {
								StartCoroutine (waktu_k5d2 ());
							}
					}


				//--rumah3

				if (br.rk5 [2].activeSelf == true) {
					rk5 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 350;
					uangplayerbaru.uangplayer += 350;
				}
				if (br.rk5 [2].activeSelf == true) { 
					rk5 [0].SetActive (false);
					rk5 [1].SetActive (false);
					rk5 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk5 [2].activeSelf == false && br.rk5 [2].activeSelf == false) { 
						if(br.rk5 [1].activeSelf == true && br.rk5 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk5 [0].SetActive (false);
							rk5 [1].SetActive (false);
							rk5 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k5)
								StartCoroutine (waktu_k5d3 ());

					}



			}


			//kotak6-------------
			if (collider.gameObject.name == "6") {
		

				if (br.rk6 [0].activeSelf == true) {
					rk6 [0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 400;
					uangplayerbaru.uangplayer += 400;

				
				}
				else
				if (br.rk6 [0].activeSelf == true) { 
						rk6 [0].SetActive (false);
						rk6 [1].SetActive (false);
						rk6 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
					
				}
				else
				if (rk6 [0].activeSelf == false && br.rk6 [0].activeSelf == false) { 
					if (br.rk6 [1].activeSelf == true && br.rk6 [2].activeSelf == true) {
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
						rk6 [0].SetActive (false);
						rk6 [1].SetActive (false);
						rk6 [2].SetActive (false);


					} else 
								if (uangcomputerbaru.uangcomputer > r1k6) {
						StartCoroutine (waktu_k6d1 ());
					}

				}


				//--rumah2
			
				if (br.rk6 [1].activeSelf == true) {
				
					uangcomputerbaru.uangcomputer -= 400;
					uangplayerbaru.uangplayer += 400;
					rk6 [1].SetActive (false);
				}
				if (br.rk6 [1].activeSelf == true) { 
					rk6 [0].SetActive (false);
					rk6 [1].SetActive (false);
					rk6 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				
						
				}
				else
					if (rk6 [1].activeSelf == false && br.rk6 [1].activeSelf == false) { 
					if (br.rk6 [2].activeSelf == true && br.rk6 [0].activeSelf == true) {
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
						rk6 [0].SetActive (false);
						rk6 [1].SetActive (false);
						rk6 [2].SetActive (false);


					} else 
							if (uangcomputerbaru.uangcomputer > r2k6) {
						StartCoroutine (waktu_k6d2 ());
					}
					}


				//--rumah3

				if (br.rk6 [2].activeSelf == true) {
					rk6 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 400;
					uangplayerbaru.uangplayer += 400;
				}
				if (br.rk6 [2].activeSelf == true) { 
					rk6 [0].SetActive (false);
					rk6 [1].SetActive (false);
					rk6 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk6 [2].activeSelf == false && br.rk6 [2].activeSelf == false) { 
						if(br.rk6 [1].activeSelf == true && br.rk6 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk6 [0].SetActive (false);
							rk6 [1].SetActive (false);
							rk6 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k6)
							StartCoroutine (waktu_k6d3 ());

					}
			}

			

			//kotak 8
			if (collider.gameObject.name == "8") {

				if (br.rk8 [0].activeSelf == true) {
					rk8 [0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 450;
					uangplayerbaru.uangplayer += 450;


				}
				else
					if (br.rk8 [0].activeSelf == true) { 
						rk8 [0].SetActive (false);
						rk8 [1].SetActive (false);
						rk8 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk8 [0].activeSelf == false && br.rk8 [0].activeSelf == false) { 
							if (br.rk8 [1].activeSelf == true && br.rk8 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk8 [0].SetActive (false);
								rk8 [1].SetActive (false);
								rk8 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k8) {
									StartCoroutine (waktu_k8d1 ());
								}

						}


				//--rumah2

				if (br.rk8 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 450;
					uangplayerbaru.uangplayer += 450;
					rk8 [1].SetActive (false);
				}
				if (br.rk8 [1].activeSelf == true) { 
					rk8 [0].SetActive (false);
					rk8 [1].SetActive (false);
					rk8 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk8 [1].activeSelf == false && br.rk8 [1].activeSelf == false) { 
						if (br.rk8 [2].activeSelf == true && br.rk8 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk8 [0].SetActive (false);
							rk8 [1].SetActive (false);
							rk8 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k8) {
								StartCoroutine (waktu_k8d2 ());
							}
					}


				//--rumah3

				if (br.rk8 [2].activeSelf == true) {
					rk8 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 450;
					uangplayerbaru.uangplayer += 450;
				}
				if (br.rk8 [2].activeSelf == true) { 
					rk8 [0].SetActive (false);
					rk8 [1].SetActive (false);
					rk8 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk8 [2].activeSelf == false && br.rk8 [2].activeSelf == false) { 
						if(br.rk8 [1].activeSelf == true && br.rk8 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk8 [0].SetActive (false);
							rk8 [1].SetActive (false);
							rk8 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k8)
								StartCoroutine (waktu_k8d3 ());

					}



			}

			//kotak 9
			if (collider.gameObject.name == "9") {

				if (br.rk9 [0].activeSelf == true) {
					rk9 [0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 500;
					uangplayerbaru.uangplayer += 500;


				}
				else
					if (br.rk9 [0].activeSelf == true) { 
						rk9 [0].SetActive (false);
						rk9 [1].SetActive (false);
						rk9 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk9 [0].activeSelf == false && br.rk9 [0].activeSelf == false) { 
							if (br.rk9 [1].activeSelf == true && br.rk9 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk9 [0].SetActive (false);
								rk9 [1].SetActive (false);
								rk9 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k9) {
									StartCoroutine (waktu_k9d1 ());
								}

						}


				//--rumah2

				if (br.rk9 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 500;
					uangplayerbaru.uangplayer += 500;
					rk9 [1].SetActive (false);
				}
				if (br.rk9 [1].activeSelf == true) { 
					rk9 [0].SetActive (false);
					rk9 [1].SetActive (false);
					rk9 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk9 [1].activeSelf == false && br.rk9 [1].activeSelf == false) { 
						if (br.rk9 [2].activeSelf == true && br.rk9 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk9 [0].SetActive (false);
							rk9 [1].SetActive (false);
							rk9 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k9) {
								StartCoroutine (waktu_k9d2 ());
							}
					}


				//--rumah3

				if (br.rk9 [2].activeSelf == true) {
					rk9 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 500;
					uangplayerbaru.uangplayer += 500;
				}
				if (br.rk9 [2].activeSelf == true) { 
					rk9 [0].SetActive (false);
					rk9 [1].SetActive (false);
					rk9 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk9 [2].activeSelf == false && br.rk9 [2].activeSelf == false) { 
						if(br.rk9 [1].activeSelf == true && br.rk9 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk9 [0].SetActive (false);
							rk9 [1].SetActive (false);
							rk9 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k9)
								StartCoroutine (waktu_k9d3 ());

					}



			}
			//kotak10
			if (collider.gameObject.name == "10") {

				if (br.rk10 [0].activeSelf == true) {
					rk10[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 550;
					uangplayerbaru.uangplayer += 550;


				}
				else
					if (br.rk10 [0].activeSelf == true) { 
						rk10 [0].SetActive (false);
						rk10 [1].SetActive (false);
						rk10 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk10 [0].activeSelf == false && br.rk10 [0].activeSelf == false) { 
							if (br.rk10 [1].activeSelf == true && br.rk10 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk10 [0].SetActive (false);
								rk10 [1].SetActive (false);
								rk10 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k10) {
									StartCoroutine (waktu_k10d1 ());
								}

						}


				//--rumah2

				if (br.rk10 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 550;
					uangplayerbaru.uangplayer += 550;
					rk10 [1].SetActive (false);
				}
				if (br.rk10 [1].activeSelf == true) { 
					rk10 [0].SetActive (false);
					rk10 [1].SetActive (false);
					rk10 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk10 [1].activeSelf == false && br.rk10 [1].activeSelf == false ) { 
						if (br.rk10 [2].activeSelf == true && br.rk10 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk10 [0].SetActive (false);
							rk10 [1].SetActive (false);
							rk10 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k10) {
								StartCoroutine (waktu_k10d2 ());
							}
					}


				//--rumah3

				if (br.rk10 [2].activeSelf == true) {
					rk10 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 550;
					uangplayerbaru.uangplayer += 550;
				}
				if (br.rk10 [2].activeSelf == true) { 
					rk10 [0].SetActive (false);
					rk10 [1].SetActive (false);
					rk10 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk10 [2].activeSelf == false && br.rk10 [2].activeSelf == false) { 
						if(br.rk10 [1].activeSelf == true && br.rk10 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk10 [0].SetActive (false);
							rk10 [1].SetActive (false);
							rk10 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k10)
								StartCoroutine (waktu_k10d3 ());

					}



			}


			//kotakpulausumatra
			if (collider.gameObject.name == "pulausumatra") {
				if (br.kotakpulausumatra [0].activeSelf == true) {
					uangcomputerbaru.uangcomputer -= 450;
					uangplayerbaru.uangplayer += 450;
				}
				else
					if (kotakpulausumatra[0].activeSelf==true) {

						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
					}
					else
						StartCoroutine (waktu_pulausumatra());

			}	
			//kotak 12
			if (collider.gameObject.name == "12") {

				if (br.rk12 [0].activeSelf == true) {
					rk12[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 600;
					uangplayerbaru.uangplayer += 600;


				}
				else
					if (br.rk12 [0].activeSelf == true) { 
						rk12 [0].SetActive (false);
						rk12 [1].SetActive (false);
						rk12 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk12 [0].activeSelf == false && br.rk12 [0].activeSelf == false) { 
							if (br.rk12 [1].activeSelf == true && br.rk12 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk12 [0].SetActive (false);
								rk12 [1].SetActive (false);
								rk12 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k12) {
									StartCoroutine (waktu_k12d1 ());
								}

						}


				//--rumah2

				if (br.rk12 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 600;
					uangplayerbaru.uangplayer += 600;
					rk12 [1].SetActive (false);
				}
				if (br.rk12 [1].activeSelf == true) { 
					rk12 [0].SetActive (false);
					rk12 [1].SetActive (false);
					rk12 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk12 [1].activeSelf == false && br.rk12 [1].activeSelf == false) { 
						if (br.rk12 [2].activeSelf == true && br.rk12 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk12 [0].SetActive (false);
							rk12 [1].SetActive (false);
							rk12 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k12) {
								StartCoroutine (waktu_k12d2 ());
							}
					}


				//--rumah3

				if (br.rk12 [2].activeSelf == true) {
					rk12 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 600;
					uangplayerbaru.uangplayer += 600;
				}
				if (br.rk12 [2].activeSelf == true) { 
					rk12 [0].SetActive (false);
					rk12 [1].SetActive (false);
					rk12 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk12 [2].activeSelf == false && br.rk12 [2].activeSelf == false) { 
						if(br.rk12 [1].activeSelf == true && br.rk12 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk12 [0].SetActive (false);
							rk12 [1].SetActive (false);
							rk12 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k12)
								StartCoroutine (waktu_k12d3 ());

					}



			}


			//kotak 13
			if (collider.gameObject.name == "13") {

				if (br.rk13 [0].activeSelf == true) {
					rk13[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 650;
					uangplayerbaru.uangplayer += 650;


				}
				else
					if (br.rk13 [0].activeSelf == true) { 
						rk13 [0].SetActive (false);
						rk13 [1].SetActive (false);
						rk13 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk13 [0].activeSelf == false && br.rk13 [0].activeSelf == false) { 
							if (br.rk13 [1].activeSelf == true && br.rk13 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk13 [0].SetActive (false);
								rk13 [1].SetActive (false);
								rk13 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k13) {
									StartCoroutine (waktu_k13d1 ());
								}

						}


				//--rumah2

				if (br.rk13 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 650;
					uangplayerbaru.uangplayer += 650;
					rk13 [1].SetActive (false);
				}
				if (br.rk13 [1].activeSelf == true) { 
					rk13 [0].SetActive (false);
					rk13 [1].SetActive (false);
					rk13 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk13 [1].activeSelf == false && br.rk13 [1].activeSelf == false) { 
						if (br.rk13 [2].activeSelf == true && br.rk13 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk13 [0].SetActive (false);
							rk13 [1].SetActive (false);
							rk13 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k13) {
								StartCoroutine (waktu_k13d2 ());
							}
					}


				//--rumah3

				if (br.rk13 [2].activeSelf == true) {
					rk13 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 650;
					uangplayerbaru.uangplayer += 650;
				}
				if (br.rk13 [2].activeSelf == true) { 
					rk13 [0].SetActive (false);
					rk13 [1].SetActive (false);
					rk13 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk13 [2].activeSelf == false && br.rk13 [2].activeSelf == false) { 
						if(br.rk12 [1].activeSelf == true && br.rk13 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk13 [0].SetActive (false);
							rk13 [1].SetActive (false);
							rk13 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k13)
								StartCoroutine (waktu_k13d3 ());

					}



			}

			//soal2 bagian-sumatra
	

			//kotak16--------------
			if (collider.gameObject.name == "16") {

				if (br.rk16 [0].activeSelf == true) {
					rk16[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 700;
					uangplayerbaru.uangplayer += 700;


				}
				else
					if (br.rk16 [0].activeSelf == true) { 
						rk16 [0].SetActive (false);
						rk16 [1].SetActive (false);
						rk16 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk16 [0].activeSelf == false && br.rk16 [0].activeSelf == false) { 
							if (br.rk16 [1].activeSelf == true && br.rk16 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk16 [0].SetActive (false);
								rk16 [1].SetActive (false);
								rk16 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k16) {
									StartCoroutine (waktu_k16d1 ());
								}

						}


				//--rumah2

				if (br.rk16 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 700;
					uangplayerbaru.uangplayer += 700;
					rk16 [1].SetActive (false);
				}
				if (br.rk16 [1].activeSelf == true) { 
					rk16 [0].SetActive (false);
					rk16 [1].SetActive (false);
					rk16 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk16 [1].activeSelf == false && br.rk16 [1].activeSelf == false) { 
						if (br.rk16 [2].activeSelf == true && br.rk16 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk16 [0].SetActive (false);
							rk16 [1].SetActive (false);
							rk16 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k16) {
								StartCoroutine (waktu_k16d2 ());
							}
					}


				//--rumah3

				if (br.rk16 [2].activeSelf == true) {
					rk16 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 700;
					uangplayerbaru.uangplayer += 700;
				}
				if (br.rk16 [2].activeSelf == true) { 
					rk16 [0].SetActive (false);
					rk16 [1].SetActive (false);
					rk16 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk16 [2].activeSelf == false && br.rk16 [2].activeSelf == false) { 
						if(br.rk16 [1].activeSelf == true && br.rk16 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk16 [0].SetActive (false);
							rk16 [1].SetActive (false);
							rk16 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k16)
								StartCoroutine (waktu_k16d3 ());

					}



			}
			//kotak 17
			if (collider.gameObject.name == "17") {

				if (br.rk17 [0].activeSelf == true) {
					rk17[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 750;
					uangplayerbaru.uangplayer += 750;


				}
				else
					if (br.rk17 [0].activeSelf == true) { 
						rk17 [0].SetActive (false);
						rk17 [1].SetActive (false);
						rk17 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk17 [0].activeSelf == false && br.rk17 [0].activeSelf == false) { 
							if (br.rk17 [1].activeSelf == true && br.rk17 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk17 [0].SetActive (false);
								rk17 [1].SetActive (false);
								rk17 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k17) {
									StartCoroutine (waktu_k17d1 ());
								}

						}


				//--rumah2

				if (br.rk17 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 750;
					uangplayerbaru.uangplayer += 750;
					rk17 [1].SetActive (false);
				}
				if (br.rk17 [1].activeSelf == true) { 
					rk17 [0].SetActive (false);
					rk17 [1].SetActive (false);
					rk17 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk17 [1].activeSelf == false && br.rk17 [1].activeSelf == false) { 
						if (br.rk17 [2].activeSelf == true && br.rk17 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk17 [0].SetActive (false);
							rk17 [1].SetActive (false);
							rk17 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k17) {
								StartCoroutine (waktu_k17d2 ());
							}
					}


				//--rumah3

				if (br.rk17 [2].activeSelf == true) {
					rk17 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 750;
					uangplayerbaru.uangplayer += 750;
				}
				if (br.rk17 [2].activeSelf == true) { 
					rk17 [0].SetActive (false);
					rk17 [1].SetActive (false);
					rk17 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk17 [2].activeSelf == false && br.rk17 [2].activeSelf == false) { 
						if(br.rk17 [1].activeSelf == true && br.rk17 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk17 [0].SetActive (false);
							rk17 [1].SetActive (false);
							rk17 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k17)
								StartCoroutine (waktu_k17d3 ());

					}



			}



			//kotak18-------------
			if (collider.gameObject.name == "18") {

				if (br.rk18 [0].activeSelf == true) {
					rk18[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 800;
					uangplayerbaru.uangplayer += 800;


				}
				else
					if (br.rk18 [0].activeSelf == true) { 
						rk18 [0].SetActive (false);
						rk18 [1].SetActive (false);
						rk18 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk18 [0].activeSelf == false && br.rk18 [0].activeSelf == false) { 
							if (br.rk18 [1].activeSelf == true && br.rk18 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk18 [0].SetActive (false);
								rk18 [1].SetActive (false);
								rk18 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k18) {
									StartCoroutine (waktu_k18d1 ());
								}

						}


				//--rumah2

				if (br.rk18 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 800;
					uangplayerbaru.uangplayer += 800;
					rk18 [1].SetActive (false);
				}
				if (br.rk18 [1].activeSelf == true) { 
					rk18 [0].SetActive (false);
					rk18 [1].SetActive (false);
					rk18 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk18 [1].activeSelf == false && br.rk18 [1].activeSelf == false) { 
						if (br.rk18 [2].activeSelf == true && br.rk18 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk18 [0].SetActive (false);
							rk18 [1].SetActive (false);
							rk18 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k18) {
								StartCoroutine (waktu_k18d2 ());
							}
					}


				//--rumah3

				if (br.rk18 [2].activeSelf == true) {
					rk18 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 800;
					uangplayerbaru.uangplayer += 800;
				}
				if (br.rk18 [2].activeSelf == true) { 
					rk18 [0].SetActive (false);
					rk18 [1].SetActive (false);
					rk18 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk18 [2].activeSelf == false && br.rk18 [2].activeSelf == false) { 
						if(br.rk18 [1].activeSelf == true && br.rk18 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk18 [0].SetActive (false);
							rk18 [1].SetActive (false);
							rk18 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k18)
								StartCoroutine (waktu_k18d3 ());

					}



			}



			//soal3-bagiansulewesi

			//pulau sulewesi---------
			if (collider.gameObject.name == "pulausulewesi") {
				if (br.kotakpulausulewesi [0].activeSelf == true) {
					uangcomputerbaru.uangcomputer -= 450;
					uangplayerbaru.uangplayer += 450;
				}
				else
					if (kotakpulausulewesi[0].activeSelf==true) {
						
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
					}
					else
						StartCoroutine (waktu_pulausulewesi());

			}	

			//kotak20---------
			if (collider.gameObject.name == "20") {

				if (br.rk20 [0].activeSelf == true) {
					rk20[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 850;
					uangplayerbaru.uangplayer += 850;


				}
				else
					if (br.rk20 [0].activeSelf == true) { 
						rk20 [0].SetActive (false);
						rk20 [1].SetActive (false);
						rk20 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk20 [0].activeSelf == false && br.rk20 [0].activeSelf == false) { 
							if (br.rk20 [1].activeSelf == true && br.rk20 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk20 [0].SetActive (false);
								rk20 [1].SetActive (false);
								rk20 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k20) {
									StartCoroutine (waktu_k20d1 ());
								}

						}


				//--rumah2

				if (br.rk20 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 850;
					uangplayerbaru.uangplayer += 850;
					rk20 [1].SetActive (false);
				}
				if (br.rk20 [1].activeSelf == true) { 
					rk20 [0].SetActive (false);
					rk20 [1].SetActive (false);
					rk20 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk20 [1].activeSelf == false && br.rk20 [1].activeSelf == false) { 
						if (br.rk20 [2].activeSelf == true && br.rk20 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk20 [0].SetActive (false);
							rk20 [1].SetActive (false);
							rk20 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k20) {
								StartCoroutine (waktu_k20d2 ());
							}
					}


				//--rumah3

				if (br.rk20 [2].activeSelf == true) {
					rk20 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 850;
					uangplayerbaru.uangplayer += 850;
				}
				if (br.rk20 [2].activeSelf == true) { 
					rk20 [0].SetActive (false);
					rk20 [1].SetActive (false);
					rk20 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk20 [2].activeSelf == false && br.rk20 [2].activeSelf == false) { 
						if(br.rk20 [1].activeSelf == true && br.rk20 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk20 [0].SetActive (false);
							rk20 [1].SetActive (false);
							rk20 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k20)
								StartCoroutine (waktu_k20d3 ());

					}



			}


			//kotak21------------
			if (collider.gameObject.name == "21") {

				if (br.rk21 [0].activeSelf == true) {
					rk21[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 900;
					uangplayerbaru.uangplayer += 900;


				}
				else
					if (br.rk21 [0].activeSelf == true) { 
						rk21 [0].SetActive (false);
						rk21 [1].SetActive (false);
						rk21 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk21 [0].activeSelf == false && br.rk21 [0].activeSelf == false) { 
							if (br.rk21 [1].activeSelf == true && br.rk21 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk21 [0].SetActive (false);
								rk21 [1].SetActive (false);
								rk21 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k21) {
									StartCoroutine (waktu_k21d1 ());
								}

						}


				//--rumah2

				if (br.rk21 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 900;
					uangplayerbaru.uangplayer += 900;
					rk21 [1].SetActive (false);
				}
				if (br.rk21 [1].activeSelf == true) { 
					rk21 [0].SetActive (false);
					rk21 [1].SetActive (false);
					rk21 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk21 [1].activeSelf == false && br.rk21 [1].activeSelf == false) { 
						if (br.rk21 [2].activeSelf == true && br.rk21 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk21 [0].SetActive (false);
							rk21 [1].SetActive (false);
							rk21 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k21) {
								StartCoroutine (waktu_k21d2 ());
							}
					}


				//--rumah3

				if (br.rk21 [2].activeSelf == true) {
					rk21 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 900;
					uangplayerbaru.uangplayer += 900;
				}
				if (br.rk21 [2].activeSelf == true) { 
					rk21 [0].SetActive (false);
					rk21 [1].SetActive (false);
					rk21 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk21 [2].activeSelf == false && br.rk21 [2].activeSelf == false) { 
						if(br.rk21 [1].activeSelf == true && br.rk21 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk21 [0].SetActive (false);
							rk21 [1].SetActive (false);
							rk21 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k21)
								StartCoroutine (waktu_k21d3 ());

					}



			}
			//kotak22--------
			if (collider.gameObject.name == "22") {

				if (br.rk22 [0].activeSelf == true) {
					rk22[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 950;
					uangplayerbaru.uangplayer += 950;


				}
				else
					if (br.rk22 [0].activeSelf == true) { 
						rk22 [0].SetActive (false);
						rk22 [1].SetActive (false);
						rk22 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk22 [0].activeSelf == false && br.rk22 [0].activeSelf == false) { 
							if (br.rk22 [1].activeSelf == true && br.rk22 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk22 [0].SetActive (false);
								rk22 [1].SetActive (false);
								rk22 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k22) {
									StartCoroutine (waktu_k22d1 ());
								}

						}


				//--rumah2

				if (br.rk22 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 950;
					uangplayerbaru.uangplayer += 950;
					rk22 [1].SetActive (false);
				}
				if (br.rk22 [1].activeSelf == true) { 
					rk22 [0].SetActive (false);
					rk22 [1].SetActive (false);
					rk22 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk22 [1].activeSelf == false && br.rk22 [1].activeSelf == false) { 
						if (br.rk22 [2].activeSelf == true && br.rk22 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk22 [0].SetActive (false);
							rk22 [1].SetActive (false);
							rk22 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k22) {
								StartCoroutine (waktu_k22d2 ());
							}
					}


				//--rumah3

				if (br.rk22 [2].activeSelf == true) {
					rk22 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 950;
					uangplayerbaru.uangplayer += 950;
				}
				if (br.rk22 [2].activeSelf == true) { 
					rk22 [0].SetActive (false);
					rk22 [1].SetActive (false);
					rk22 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk22 [2].activeSelf == false && br.rk22 [2].activeSelf == false) { 
						if(br.rk22 [1].activeSelf == true && br.rk22 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk22 [0].SetActive (false);
							rk22 [1].SetActive (false);
							rk22 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k22)
								StartCoroutine (waktu_k22d3 ());

					}



			}

			//kotak24----------
			if (collider.gameObject.name == "24") {

				if (br.rk24 [0].activeSelf == true) {
					rk24[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 1000;
					uangplayerbaru.uangplayer += 1000;


				}
				else
					if (br.rk24 [0].activeSelf == true) { 
						rk24 [0].SetActive (false);
						rk24 [1].SetActive (false);
						rk24 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk24 [0].activeSelf == false && br.rk24 [0].activeSelf == false) { 
							if (br.rk24 [1].activeSelf == true && br.rk24 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk24 [0].SetActive (false);
								rk24 [1].SetActive (false);
								rk24 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k24) {
									StartCoroutine (waktu_k24d1 ());
								}

						}


				//--rumah2

				if (br.rk24 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 1000;
					uangplayerbaru.uangplayer += 1000;
					rk24 [1].SetActive (false);
				}
				if (br.rk24 [1].activeSelf == true) { 
					rk24 [0].SetActive (false);
					rk24 [1].SetActive (false);
					rk24 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk24 [1].activeSelf == false && br.rk24 [1].activeSelf == false) { 
						if (br.rk24 [2].activeSelf == true && br.rk24 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk24 [0].SetActive (false);
							rk24 [1].SetActive (false);
							rk24 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k24) {
								StartCoroutine (waktu_k24d2 ());
							}
					}


				//--rumah3

				if (br.rk24 [2].activeSelf == true) {
					rk24 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 1000;
					uangplayerbaru.uangplayer += 1000;
				}
				if (br.rk24 [2].activeSelf == true) { 
					rk24 [0].SetActive (false);
					rk24 [1].SetActive (false);
					rk24 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk24 [2].activeSelf == false && br.rk24 [2].activeSelf == false) { 
						if(br.rk24 [1].activeSelf == true && br.rk24 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk24 [0].SetActive (false);
							rk24 [1].SetActive (false);
							rk24 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k24)
								StartCoroutine (waktu_k24d3 ());

					}



			}
			//soal4-bagiankalimantan
		
			//pulaukalimantan
			if (collider.gameObject.name == "pulaukalimantan") {
				if (br.kotakpulaukalimantan [0].activeSelf == true) {
					uangcomputerbaru.uangcomputer -= 450;
					uangplayerbaru.uangplayer += 450;
				}
				else
					if (kotakpulaukalimantan[0].activeSelf==true) {

						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
					}
					else
						StartCoroutine (waktu_pulaukalimantan());

			}	

			//kotak27----------
			if (collider.gameObject.name == "27") {

				if (br.rk27 [0].activeSelf == true) {
					rk27[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 1050;
					uangplayerbaru.uangplayer += 1050;


				}
				else
					if (br.rk27 [0].activeSelf == true) { 
						rk27 [0].SetActive (false);
						rk27 [1].SetActive (false);
						rk27 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk27 [0].activeSelf == false && br.rk27 [0].activeSelf == false) { 
							if (br.rk27 [1].activeSelf == true && br.rk27 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk27 [0].SetActive (false);
								rk27 [1].SetActive (false);
								rk27 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k27) {
									StartCoroutine (waktu_k27d1 ());
								}

						}


				//--rumah2

				if (br.rk27 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 1050;
					uangplayerbaru.uangplayer += 1050;
					rk27 [1].SetActive (false);
				}
				if (br.rk27 [1].activeSelf == true) { 
					rk27 [0].SetActive (false);
					rk27 [1].SetActive (false);
					rk22 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk27 [1].activeSelf == false && br.rk27 [1].activeSelf == false) { 
						if (br.rk27 [2].activeSelf == true && br.rk27 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk27 [0].SetActive (false);
							rk27 [1].SetActive (false);
							rk27 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k27) {
								StartCoroutine (waktu_k27d2 ());
							}
					}


				//--rumah3

				if (br.rk27 [2].activeSelf == true) {
					rk27 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 1050;
					uangplayerbaru.uangplayer += 1050;
				}
				if (br.rk27 [2].activeSelf == true) { 
					rk27 [0].SetActive (false);
					rk27 [1].SetActive (false);
					rk27 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk27 [2].activeSelf == false && br.rk27 [2].activeSelf == false) { 
						if(br.rk27 [1].activeSelf == true && br.rk27 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk27 [0].SetActive (false);
							rk27 [1].SetActive (false);
							rk27 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k27)
								StartCoroutine (waktu_k27d3 ());

					}



			}
			/*
			//kotak28-----------
			if (collider.gameObject.name == "28") {

				if (br.rk28 [0].activeSelf == true) {
					rk28[0].SetActive (false);
					uangcomputerbaru.uangcomputer -= 1100;
					uangplayerbaru.uangplayer += 1100;


				}
				else
					if (br.rk28 [0].activeSelf == true) { 
						rk28 [0].SetActive (false);
						rk28 [1].SetActive (false);
						rk28 [2].SetActive (false);
						uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;

					}
					else
						if (rk28 [0].activeSelf == false && br.rk28 [0].activeSelf == false) { 
							if (br.rk28 [1].activeSelf == true && br.rk28 [2].activeSelf == true) {
								uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
								rk28 [0].SetActive (false);
								rk28 [1].SetActive (false);
								rk28 [2].SetActive (false);


							} else 
								if (uangcomputerbaru.uangcomputer > r1k28) {
									StartCoroutine (waktu_k28d1 ());
								}

						}


				//--rumah2

				if (br.rk28 [1].activeSelf == true) {

					uangcomputerbaru.uangcomputer -= 1100;
					uangplayerbaru.uangplayer += 1100;
					rk28 [1].SetActive (false);
				}
				if (br.rk28 [1].activeSelf == true) { 
					rk28 [0].SetActive (false);
					rk28 [1].SetActive (false);
					rk28 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;




				}
				else
					if (rk28 [1].activeSelf == false && br.rk28 [1].activeSelf == false) { 
						if (br.rk28 [2].activeSelf == true && br.rk28 [0].activeSelf == true) {
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk28 [0].SetActive (false);
							rk28 [1].SetActive (false);
							rk28 [2].SetActive (false);


						} else 
							if (uangcomputerbaru.uangcomputer > r2k28) {
								StartCoroutine (waktu_k28d2 ());
							}
					}


				//--rumah3

				if (br.rk28 [2].activeSelf == true) {
					rk28 [2].SetActive (false);
					uangcomputerbaru.uangcomputer -= 1100;
					uangplayerbaru.uangplayer += 1100;
				}
				if (br.rk28 [2].activeSelf == true) { 
					rk28 [0].SetActive (false);
					rk28 [1].SetActive (false);
					rk28 [2].SetActive (false);
					uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;


				}
				else
					if (rk28 [2].activeSelf == false && br.rk28 [2].activeSelf == false) { 
						if(br.rk28 [1].activeSelf == true && br.rk28 [0].activeSelf == true){
							uangcomputerbaru.uangcomputer = uangcomputerbaru.uangcomputer - 0;
							rk28 [0].SetActive (false);
							rk28 [1].SetActive (false);
							rk28 [2].SetActive (false);


						}
						else
							if (uangcomputerbaru.uangcomputer > r3k28)
								StartCoroutine (waktu_k28d3 ());

					}



			}

			*/
}


	}
	public void OnTriggerExit(Collider col){
		
		if (col.gameObject.name == "penjara") {
			penjara.SetActive (false);
		}
	}
}
