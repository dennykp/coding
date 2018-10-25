using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;

public class buildrumah : MonoBehaviour {
	public GameObject[] paneldenda;
	private AudioSource soundcoinbonus;
	public AudioClip coinbonussound;
	public GameObject showgambarbonus;
	public GameObject showgambarpajak;
	public GameObject[] showpilihan;
	public static int countpulau;
	public GameObject gambarstart;
	private AudioSource langkah;
	public AudioClip langkahsound;
	public GameObject showgambarquiz;
	public GameObject gambarpenjara;
	int a,b,m,x,hasil;
	private AudioSource soundbuy;
	public AudioClip buysound;
	public GameObject showpanelwin;
	public GameObject showflare;
	public GameObject bangkrut;
	public  static double uangpemain;
	public double winner;
	public static double bank;
	public Text counttext;
	public Text counttext2;
	public Text banktext;
	public GameObject[] hidekontenafterbuy;
	public GameObject hidekuis;
	public GameObject uangmono;
	public GameObject uangmono2;
	public GameObject uangmono3;
	public GameObject uangmono4;
	public GameObject uangmono5;
	public GameObject uangmono6;
	public GameObject uangmono7;
	public GameObject uangmono8;
	public Text win;
	public dadut dda;
	//uang rumah
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
	//bangunpulau
	public double hargapulaujawa = 1100;
	public double hargapulaukalimantan = 1300;
	public double hargapulausumatra = 1200;
	public double hargapulausulewesi = 950;
	//rumah
	public Rigidbody rumah1rigid;
	public Rigidbody rumah2rigid;
	public Rigidbody rumah3rigid;
	public Rigidbody rumah1k2rigid;
	public Rigidbody rumah2k2rigid;
	public Rigidbody rumah3k2rigid;
	public Rigidbody rumah1k3rigid;
	public Rigidbody rumah2k3rigid;
	public Rigidbody rumah3k3rigid;
	public Rigidbody rumah1k4rigid;
	public Rigidbody rumah2k4rigid;
	public Rigidbody rumah3k4rigid;
	public Rigidbody rumah1k5rigid;
	public Rigidbody rumah2k5rigid;
	public Rigidbody rumah3k5rigid;
	public Rigidbody rumah1k6rigid;
	public Rigidbody rumah2k6rigid;
	public Rigidbody rumah3k6rigid;
	public Rigidbody rumah1k7rigid;
	public Rigidbody rumah2k7rigid;
	public Rigidbody rumah3k7rigid;
	public Rigidbody rumah1k8rigid;
	public Rigidbody rumah2k8rigid;
	public Rigidbody rumah3k8rigid;
	public Rigidbody rumah1k9rigid;
	public Rigidbody rumah2k9rigid;
	public Rigidbody rumah3k9rigid;
	public Rigidbody rumah1k10rigid;
	public Rigidbody rumah2k10rigid;
	public Rigidbody rumah3k10rigid;
	public Rigidbody rumah1k11rigid;
	public Rigidbody rumah2k11rigid;
	public Rigidbody rumah3k11rigid;
	public Rigidbody rumah1k12rigid;
	public Rigidbody rumah2k12rigid;
	public Rigidbody rumah3k12rigid;
	public Rigidbody rumah1k13rigid;
	public Rigidbody rumah2k13rigid;
	public Rigidbody rumah3k13rigid;
	public Rigidbody rumah1k16rigid;
	public Rigidbody rumah2k16rigid;
	public Rigidbody rumah3k16rigid;
	public Rigidbody rumah1k17rigid;
	public Rigidbody rumah2k17rigid;
	public Rigidbody rumah3k17rigid;
	public Rigidbody rumah1k18rigid;
	public Rigidbody rumah2k18rigid;
	public Rigidbody rumah3k18rigid;
	public Rigidbody rumah1k20rigid;
	public Rigidbody rumah2k20rigid;
	public Rigidbody rumah3k20rigid;
	public Rigidbody rumah1k21rigid;
	public Rigidbody rumah2k21rigid;
	public Rigidbody rumah3k21rigid;
	public Rigidbody rumah1k22rigid;
	public Rigidbody rumah2k22rigid;
	public Rigidbody rumah3k22rigid;
	public Rigidbody rumah1k24rigid;
	public Rigidbody rumah2k24rigid;
	public Rigidbody rumah3k24rigid;
	public Rigidbody rumah1k27rigid;
	public Rigidbody rumah2k27rigid;
	public Rigidbody rumah3k27rigid;
	public Rigidbody rumah1k28rigid;
	public Rigidbody rumah2k28rigid;
	public Rigidbody rumah3k28rigid;
	public Rigidbody pulaujawa;
	public Rigidbody pulaukalimantan;
	public Rigidbody pulausumatra;
	public Rigidbody pulausulewesi;

	public PLAYER namavar;
	public PLAYER player2;
	public Button k1r1;
	public Button k1r2;
	public Button k1r3;
	public Button k2r1;
	public Button k2r2;
	public Button k2r3;
	public Button[] buttonbeli;
	public Button btnpulaujawa;
	public Button btnpulausumatra;
	public Button btnpulausulewesi;
	public Button btnpulaukalimantan;
	public PLAYER aa;
	//variabel show
	public GameObject rumahkartu1;
	public GameObject rumahkartu2;
	public GameObject rumahkartu3;
	public GameObject kpulaujawa;
	public GameObject rumahkartu5;
	public GameObject rumahkartu6;
	public GameObject rumahkartu8;
	public GameObject rumahkartu9;
	public GameObject rumahkartu10;
	public GameObject kpulausumatra;
	public GameObject rumahkartu12;
	public GameObject rumahkartu13;
	public GameObject rumahkartu16;
	public GameObject rumahkartu17;
	public GameObject rumahkartu18;
	public GameObject kpulausulewesi;
	public GameObject rumahkartu20;
	public GameObject rumahkartu21;
	public GameObject rumahkartu22;
	public GameObject rumahkartu24;
	public GameObject kpulaukalimantan;
	public GameObject rumahkartu27;
	public GameObject rumahkartu28;
	public GameObject soal1;
	public GameObject soal2;
	public GameObject soal3;
	public GameObject soal4;
	public aidetect cek;
	public GameObject[] close;
	public GameObject penjara;

	public double kk;
	private AudioSource penjarasound;
	public AudioClip soundpenjara;
	private AudioSource showpanelsuara;
	public AudioClip showsuarapanel;
	public GameObject[] rk1;
	public GameObject[] rk2;
	public GameObject[] rk3;
	public GameObject[] kotakpulaujawa;
	public GameObject[] rk5;
	public GameObject[] rk6;
	public GameObject[] soal11;
	public GameObject[] rk8;
	public GameObject[] rk9;
	public GameObject[] rk10;
	public GameObject[] kotakpulausumatra;
	public GameObject[] rk12;
	public GameObject[] rk13;
	public GameObject[] soal22;
	public GameObject[] rk16;
	public GameObject[] rk17;
	public GameObject[] rk18;
	public GameObject[] kotakpulausulewesi;
	public GameObject[] rk20;
	public GameObject[] rk21;
	public GameObject[] soal33;
	public GameObject[] rk22;
	public GameObject[] pajak;
	public GameObject[] rk24;
	public GameObject[] kotakpulaukalimantan;
	public GameObject[] soal44;
	public GameObject[] rk27;
	public GameObject[] rk28;
	public prosessoal ambilsoal;
	public setuang st;
	// Use this for initialization
	void Start () {
		
		Debug.Log (countpulau);
	

	
		bank = 22500;
	

		}

	void Awake () {
		soundbuy =  GetComponent<AudioSource>();
		penjarasound = GetComponent<AudioSource>();
		showpanelsuara = GetComponent<AudioSource>();
	
			langkah = GetComponent<AudioSource>();
		soundcoinbonus = GetComponent<AudioSource> ();
	}

	public void closekonten(){
		close [0].SetActive (false);
		close [1].SetActive (false);
		close [2].SetActive (false);
		close [3].SetActive (false);
		close [4].SetActive (false);

	}


	public void uangnambah(int nilai){
		
			uangplayerbaru.uangplayer += nilai;
				counttext.text = "" + uangplayerbaru.uangplayer;
		setcount ();
		}
	//kotak1----------------
	IEnumerator waktu_k1d1(){

		yield return new WaitForSeconds (1);
		rk1[0].SetActive (true);

		uangplayerbaru.uangplayer= uangplayerbaru.uangplayer - rumah1;

		setcount ();
	}
	IEnumerator waktu_k1d2(){

		yield return new WaitForSeconds (2);
		rk1[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - rumah2;
		setcount ();
	}
	IEnumerator waktu_k1d3(){

		yield return new WaitForSeconds (3);
		rk1[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - rumah3;
		setcount ();
	}
	//kotak2-----------------
	IEnumerator waktu_k2d1(){

		yield return new WaitForSeconds (1);
		rk2[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k2;
		setcount ();
	}
	IEnumerator waktu_k2d2(){

		yield return new WaitForSeconds (2);
		rk2[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k2;
		setcount ();
	}
	IEnumerator waktu_k2d3(){

		yield return new WaitForSeconds (3);
		rk2[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k2;
		setcount ();
	}
	//kotak3------------------
	IEnumerator waktu_k3d1(){

		yield return new WaitForSeconds (1);
		rk3[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k3;
		setcount ();
	}
	IEnumerator waktu_k3d2(){

		yield return new WaitForSeconds (2);
		rk3[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k3;
		setcount ();
	}
	IEnumerator waktu_k3d3(){

		yield return new WaitForSeconds (3);
		rk3[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k3;
		setcount ();
	}
	//kotakpulaujawa

	//kotak5----------
	IEnumerator waktu_k5d1(){

		yield return new WaitForSeconds (1);
		rk5[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k5;
		setcount ();
	}
	IEnumerator waktu_k5d2(){

		yield return new WaitForSeconds (2);
		rk5[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k5;
		setcount ();
	}
	IEnumerator waktu_k5d3(){

		yield return new WaitForSeconds (3);
		rk5[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k5;
		setcount ();
	}

	//kotak6-------------

	IEnumerator waktu_k6d1(){

		yield return new WaitForSeconds (1);
		rk6[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k6;
		setcount ();
	}
	IEnumerator waktu_k6d2(){

		yield return new WaitForSeconds (2);
		rk6[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k6;
		setcount ();
	}
	IEnumerator waktu_k6d3(){

		yield return new WaitForSeconds (3);
		rk6[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k6;
		setcount ();
	}
	//soal1
	IEnumerator delaysoal1(){
		yield return new WaitForSeconds (1);
		showgambarquiz.SetActive (true);
		yield return new WaitForSeconds (3);
		showgambarquiz.SetActive (false);
	}
	IEnumerator waktu_soal1(){
		
		yield return new WaitForSeconds (4);

		soal1.SetActive (true);
	}

	//kotak8
	IEnumerator waktu_k8d1(){

		yield return new WaitForSeconds (1);
		rk8[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k8;
		setcount ();
	}
	IEnumerator waktu_k8d2(){

		yield return new WaitForSeconds (2);
		rk8[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k8;
		setcount ();
	}
	IEnumerator waktu_k8d3(){

		yield return new WaitForSeconds (3);
		rk8[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k8;
		setcount ();
	}
	//kotak9
	IEnumerator waktu_k9d1(){

		yield return new WaitForSeconds (1);
		rk9[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k9;
		setcount ();
	}
	IEnumerator waktu_k9d2(){

		yield return new WaitForSeconds (2);
		rk9[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k9;
		setcount ();
	}
	IEnumerator waktu_k9d3(){

		yield return new WaitForSeconds (3);
		rk9[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k9;
		setcount ();
	}
	//kotak10
	IEnumerator waktu_k10d1(){

		yield return new WaitForSeconds (1);
		rk10[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k10;
		setcount ();
	}
	IEnumerator waktu_k10d2(){

		yield return new WaitForSeconds (2);
		rk10[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k10;
		setcount ();
	}
	IEnumerator waktu_k10d3(){

		yield return new WaitForSeconds (3);
		rk10[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k10;
		setcount ();
	}
	//pulausumatra
	IEnumerator waktu_pulausumatrashow(){

		yield return new WaitForSeconds (3);
		rk10[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k10;
		setcount ();
	}
	//kotak12
	IEnumerator waktu_k12d1(){

		yield return new WaitForSeconds (1);
		rk12[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k12;
		setcount ();
	}
	IEnumerator waktu_k12d2(){

		yield return new WaitForSeconds (2);
		rk12[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k12;
		setcount ();
	}
	IEnumerator waktu_k12d3(){

		yield return new WaitForSeconds (3);
		rk12[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k12;
		setcount ();
	}
	//kotak13
	IEnumerator waktu_k13d1(){

		yield return new WaitForSeconds (1);
		rk13[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k10;
		setcount ();
	}
	IEnumerator waktu_k13d2(){

		yield return new WaitForSeconds (2);
		rk13[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k13;
		setcount ();
	}
	IEnumerator waktu_k13d3(){

		yield return new WaitForSeconds (3);
		rk13[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k13;
		setcount ();
	}
	//soal2
	IEnumerator waktu_soal2(){

		yield return new WaitForSeconds (3);

		soal2.SetActive (true);
	}
	//kotak16
	IEnumerator waktu_k16d1(){

		yield return new WaitForSeconds (1);
		rk16[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k16;
		setcount ();
	}
	IEnumerator waktu_k16d2(){

		yield return new WaitForSeconds (2);
		rk16[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k16;
		setcount ();
	}
	IEnumerator waktu_k16d3(){

		yield return new WaitForSeconds (3);
		rk16[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k16;
		setcount ();
	}
	//kotak17
	IEnumerator waktu_k17d1(){

		yield return new WaitForSeconds (1);
		rk17[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k17;
		setcount ();
	}
	IEnumerator waktu_k17d2(){

		yield return new WaitForSeconds (2);
		rk17[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k17;
		setcount ();
	}
	IEnumerator waktu_k17d3(){

		yield return new WaitForSeconds (3);
		rk17[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k17;
		setcount ();
	}
	//kotak18
	IEnumerator waktu_k18d1(){

		yield return new WaitForSeconds (1);
		rk18[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k18;
		setcount ();
	}
	IEnumerator waktu_k18d2(){

		yield return new WaitForSeconds (2);
		rk18[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k18;
		setcount ();
	}
	IEnumerator waktu_k18d3(){

		yield return new WaitForSeconds (3);
		rk18[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k18;
		setcount ();
	}
	//kotakpulausulewesi
	IEnumerator waktu_pulausulewesishow(){

		yield return new WaitForSeconds (3);
		rk18[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k18;
		setcount ();
	}
	//kotak20
	IEnumerator waktu_k20d1(){

		yield return new WaitForSeconds (1);
		rk20[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k20;
		setcount ();
	}
	IEnumerator waktu_k20d2(){

		yield return new WaitForSeconds (2);
		rk20[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k20;
		setcount ();
	}
	IEnumerator waktu_k20d3(){

		yield return new WaitForSeconds (3);
		rk20[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k20;
		setcount ();
	}
	//kotak21
	IEnumerator waktu_k21d1(){

		yield return new WaitForSeconds (1);
		rk21[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k21;
		setcount ();
	}
	IEnumerator waktu_k21d2(){

		yield return new WaitForSeconds (2);
		rk21[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k21;
		setcount ();
	}
	IEnumerator waktu_k21d3(){

		yield return new WaitForSeconds (3);
		rk21[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k21;
		setcount ();
	}
	//soal3
	IEnumerator waktu_soal3(){

		yield return new WaitForSeconds (3);

		soal3.SetActive (true);
	}
	//kotak22
	IEnumerator waktu_k22d1(){

		yield return new WaitForSeconds (1);
		rk22[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k22;
		setcount ();
	}
	IEnumerator waktu_k22d2(){

		yield return new WaitForSeconds (2);
		rk22[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k22;
		setcount ();
	}
	IEnumerator waktu_k22d3(){

		yield return new WaitForSeconds (3);
		rk22[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k22;
		setcount ();
	}
	//pajak
	IEnumerator waktu_pajak(){

		yield return new WaitForSeconds (3);
		rk22[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k22;
		setcount ();
	}
	//kotak24
	IEnumerator waktu_k24d1(){

		yield return new WaitForSeconds (1);
		rk24[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k24;
		setcount ();
	}
	IEnumerator waktu_k24d2(){

		yield return new WaitForSeconds (2);
		rk24[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k24;
		setcount ();
	}
	IEnumerator waktu_k24d3(){

		yield return new WaitForSeconds (3);
		rk24[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k24;
		setcount ();
	}
	//pulaukalimantan
	IEnumerator waktu_pulaukalimantanshow(){

		yield return new WaitForSeconds (3);
		rk24[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k24;
		setcount ();
	}
	//soal4
	IEnumerator waktu_soal4(){

		yield return new WaitForSeconds (3);

		soal4.SetActive (true);
	}
	//kotak27
	IEnumerator waktu_k27d1(){

		yield return new WaitForSeconds (1);
		rk27[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k27;
		setcount ();
	}
	IEnumerator waktu_k27d2(){

		yield return new WaitForSeconds (2);
		rk27[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k27;
		setcount ();
	}
	IEnumerator waktu_k27d3(){

		yield return new WaitForSeconds (3);
		rk27[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k27;
		setcount ();
	}
	//kotak28
	IEnumerator waktu_k28d1(){

		yield return new WaitForSeconds (1);
		rk28[0].SetActive (true);

		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r1k28;
		setcount ();
	}
	IEnumerator waktu_k28d2(){

		yield return new WaitForSeconds (2);
		rk28[1].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r2k28;
		setcount ();
	}
	IEnumerator waktu_k28d3(){

		yield return new WaitForSeconds (3);
		rk28[2].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - r3k28;
		setcount ();
	}
	IEnumerator beli_pulaujawa(){

		yield return new WaitForSeconds (1);
		kotakpulaujawa[0].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - hargapulaujawa;
		setcount ();
	}
	IEnumerator beli_pulausumatra(){

		yield return new WaitForSeconds (1);
		kotakpulausumatra[0].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - hargapulausumatra;
		setcount ();
	}
	IEnumerator beli_pulausulewesi(){

		yield return new WaitForSeconds (1);
		kotakpulausulewesi[0].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - hargapulausulewesi;
		setcount ();
	}
	IEnumerator beli_pulaukalimantan(){

		yield return new WaitForSeconds (1);
		kotakpulaukalimantan[0].SetActive (true);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - hargapulaukalimantan;
		setcount ();
	}
	//buttonbelii----------------------------------------

	public void btn(){
		StartCoroutine (waktu_k1d1 ());
		soundbuy.PlayOneShot (buysound);
		k1r1.interactable = false;
			

		}

		
	public void btn2(){
		StartCoroutine (waktu_k1d2 ());	
		soundbuy.PlayOneShot (buysound);
		k1r2.interactable = false;


		}

	public void btn3(){
		StartCoroutine (waktu_k1d3 ());	
		soundbuy.PlayOneShot (buysound);
		k1r3.interactable = false;

		}

	public void R1K2(){
		StartCoroutine (waktu_k2d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [0].interactable = false;
	}
	public void R2K2(){
		
		StartCoroutine (waktu_k2d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [1].interactable = false;
	}
	public void R3K2(){

		StartCoroutine (waktu_k2d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [2].interactable = false;
	}
	public void R1K3(){
		
		StartCoroutine (waktu_k3d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [3].interactable = false;
	}
	public void R2K3(){
		
		StartCoroutine (waktu_k3d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [4].interactable = false;
	}
	public void R3K3(){
		
		StartCoroutine (waktu_k3d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [5].interactable = false;
	}
	public void pulaujawaa(){
		StartCoroutine (beli_pulaujawa ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [6].interactable = false;

	}

	public void R1K5(){

		StartCoroutine (waktu_k5d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [7].interactable = false;
	}
	public void R2K5(){
		
		StartCoroutine (waktu_k5d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [8].interactable = false;
	}
	public void R3K5(){
		
		StartCoroutine (waktu_k5d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [9].interactable = false;
	}
	public void R1K6(){
		
		StartCoroutine (waktu_k6d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [10].interactable = false;
	}
	public void R2K6(){
		StartCoroutine (waktu_k6d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [11].interactable = false;
	}
	public void R3K6(){
		
		StartCoroutine (waktu_k6d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [12].interactable = false;
	}

	public void R1K8(){
		StartCoroutine (waktu_k8d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [13].interactable = false;
	}
	public void R2K8(){
		StartCoroutine (waktu_k8d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [14].interactable = false;
	}
	public void R3K8(){
		StartCoroutine (waktu_k8d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [15].interactable = false;
	}
	public void R1K9(){
		StartCoroutine (waktu_k9d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [16].interactable = false;
	}
	public void R2K9(){
		StartCoroutine (waktu_k9d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [17].interactable = false;
	}
	public void R3K9(){
		StartCoroutine (waktu_k9d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [18].interactable = false;
	}
	public void R1K10(){
		StartCoroutine (waktu_k10d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [19].interactable = false;
	}
	public void R2K10(){
		StartCoroutine (waktu_k10d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [20].interactable = false;
	}
	public void R3K10(){
		StartCoroutine (waktu_k10d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [21].interactable = false;
	}
	public void pulausumatraa(){
		StartCoroutine (beli_pulausumatra ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [22].interactable = false;
	}

	public void R1K12(){
		StartCoroutine (waktu_k12d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [23].interactable = false;
	}
	public void R2K12(){
		StartCoroutine (waktu_k12d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [24].interactable = false;
	}
	public void R3K12(){
		StartCoroutine (waktu_k12d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [25].interactable = false;
	}
	public void R1K13(){
		StartCoroutine (waktu_k13d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [26].interactable = false;
	}
	public void R2K13(){
		StartCoroutine (waktu_k13d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [27].interactable = false;
	}
	public void R3K13(){
		StartCoroutine (waktu_k13d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [28].interactable = false;
	}

	public void R1K16(){
		StartCoroutine (waktu_k16d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [29].interactable = false;
	}
	public void R2K16(){
		StartCoroutine (waktu_k16d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [30].interactable = false;
	}
	public void R3K16(){
		StartCoroutine (waktu_k16d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [31].interactable = false;
	}
	public void R1K17(){
		StartCoroutine (waktu_k17d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [32].interactable = false;
	}
	public void R2K17(){
		StartCoroutine (waktu_k17d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [33].interactable = false;
	}
	public void R3K17(){
		StartCoroutine (waktu_k17d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [34].interactable = false;
	}
	public void R1K18(){
		StartCoroutine (waktu_k18d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [35].interactable = false;
	}
	public void R2K18(){
		StartCoroutine (waktu_k18d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [36].interactable = false;
	}
	public void R3K18(){
		StartCoroutine (waktu_k18d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [37].interactable = false;
	}
	public void pulausulewesii(){
		StartCoroutine (beli_pulausulewesi ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [56].interactable = false;
	}
	public void R1K20(){
		StartCoroutine (waktu_k20d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [38].interactable = false;
	}
	public void R2K20(){
		StartCoroutine (waktu_k20d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [39].interactable = false;
	}
	public void R3K20(){
		StartCoroutine (waktu_k20d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [40].interactable = false;
	}
	public void R1K21(){
		StartCoroutine (waktu_k21d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [41].interactable = false;
	}
	public void R2K21(){
		StartCoroutine (waktu_k21d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [42].interactable = false;
	}
	public void R3K21(){
		StartCoroutine (waktu_k21d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [43].interactable = false;
	}
	public void R1K22(){
		StartCoroutine (waktu_k22d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [44].interactable = false;
	}
	public void R2K22(){
		StartCoroutine (waktu_k22d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [45].interactable = false;
	}
	public void R3K22(){
		StartCoroutine (waktu_k22d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [46].interactable = false;
	}
	public void R1K24(){
		StartCoroutine (waktu_k24d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [47].interactable = false;
	}
	public void R2K24(){
		StartCoroutine (waktu_k24d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [48].interactable = false;
	}
	public void R3K24(){
		StartCoroutine (waktu_k24d3());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [49].interactable = false;
	}
	public void pulaukalimantann(){
		StartCoroutine (beli_pulaukalimantan ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [57].interactable = false;
	}
	public void R1K27(){
		StartCoroutine (waktu_k27d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [50].interactable = false;
	}
	public void R2K27(){
		StartCoroutine (waktu_k27d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [51].interactable = false;
	}
	public void R3K27(){
		StartCoroutine (waktu_k27d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [52].interactable = false;
	}
	public void R1K28(){
		StartCoroutine (waktu_k28d1 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [53].interactable = false;
	}
	public void R2K28(){
		StartCoroutine (waktu_k28d2 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [54].interactable = false;
	}
	public void R3K28(){
		StartCoroutine (waktu_k28d3 ());	
		soundbuy.PlayOneShot (buysound);
		buttonbeli [55].interactable = false;
	}
	public void pulauJawa(){
		soundbuy.PlayOneShot (buysound);
		Rigidbody pulau;
		pulau = Instantiate(pulaujawa, transform.position, transform.rotation) as Rigidbody;
		pulau.velocity = transform.TransformDirection(Vector3.forward * 10);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - hargapulaujawa;
		btnpulaujawa.interactable = false;
		setcount ();
	}
	public void pulauKalimantan(){
		soundbuy.PlayOneShot (buysound);
		Rigidbody pulau;
		pulau = Instantiate(pulaukalimantan, transform.position, transform.rotation) as Rigidbody;
		pulau.velocity = transform.TransformDirection(Vector3.forward * 10);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - hargapulaukalimantan;
		btnpulaukalimantan.interactable = false;
		setcount ();
	}
	public void pulauSumatra(){
		soundbuy.PlayOneShot (buysound);
		Rigidbody pulau;
		pulau = Instantiate(pulausumatra, transform.position, transform.rotation) as Rigidbody;
		pulau.velocity = transform.TransformDirection(Vector3.forward * 10);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - hargapulausumatra;
		btnpulausumatra.interactable = false;
		setcount ();
	}
	public void pulauSulewesi(){
		soundbuy.PlayOneShot (buysound);
		Rigidbody pulau;
		pulau = Instantiate(pulausulewesi, transform.position, transform.rotation) as Rigidbody;
		pulau.velocity = transform.TransformDirection(Vector3.forward * 10);
		uangplayerbaru.uangplayer = uangplayerbaru.uangplayer - hargapulausulewesi;
		btnpulausulewesi.interactable = false;
		setcount ();
	}
	public void setcount(){
		Text sco;
		sco = Instantiate(counttext, transform.position, transform.rotation) as Text;


		counttext.text = " " + uangplayerbaru.uangplayer;
	}
	public void setcountcomputer(){
		Text scoa;
		scoa = Instantiate(counttext2, transform.position, transform.rotation) as Text;


	
	}
	public void setcount2(){
		Text sco;
		sco = Instantiate(counttext, transform.position, transform.rotation) as Text;
		hidekuis.SetActive (false);

		uangplayerbaru.uangplayer += 750;
		counttext.text = " " + uangplayerbaru.uangplayer;

	}
	//cek bangunan
	IEnumerator waktu_rumahk1(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu1.SetActive (true);
	}
	IEnumerator waktu_rumahk2(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu2.SetActive (true);

	}	
	IEnumerator waktu_rumahk3(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu3.SetActive (true);

	}
	IEnumerator waktu_rumahk5(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu5.SetActive (true);

	}
	IEnumerator waktu_rumahk6(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu6.SetActive (true);

	}
	IEnumerator waktu_rumahk8(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu8.SetActive (true);

	}
	IEnumerator waktu_rumahk9(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu9.SetActive (true);

	}
	IEnumerator waktu_rumahk10(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu10.SetActive (true);

	}
	IEnumerator waktu_rumahk12(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu12.SetActive (true);

	}
	IEnumerator waktu_rumahk13(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu13.SetActive (true);

	}
	IEnumerator waktu_rumahk16(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu16.SetActive (true);

	}
	IEnumerator waktu_rumahk17(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu17.SetActive (true);

	}
	IEnumerator waktu_rumahk18(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu18.SetActive (true);

	}
	IEnumerator waktu_rumahk20(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu20.SetActive (true);

	}
	IEnumerator waktu_rumahk21(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu21.SetActive (true);

	}
	IEnumerator waktu_rumahk22(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu22.SetActive (true);

	}
	IEnumerator waktu_rumahk24(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu24.SetActive (true);

	}
	IEnumerator waktu_rumahk27(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu27.SetActive (true);

	}
	IEnumerator waktu_rumahk28(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu28.SetActive (true);

	}
	IEnumerator waktu_pulaujawa(){

		yield return new WaitForSeconds (2);
		kpulaujawa.SetActive (true);
		showpanelsuara.PlayOneShot (showsuarapanel);
	}

	IEnumerator waktu_pulausulewesi(){

		yield return new WaitForSeconds (2);
		kpulausulewesi.SetActive (true);
	}

	IEnumerator waktu_pulausumatra(){

		yield return new WaitForSeconds (2);
		kpulausumatra.SetActive (true);
	}
	IEnumerator waktu_pulaukalimantan(){

		yield return new WaitForSeconds (2);
		kpulaukalimantan.SetActive (true);
	}
	IEnumerator waktu_penjara(){

		yield return new WaitForSeconds (2);
		penjara.SetActive (true);
	}
	IEnumerator delaybacktojail(){
		yield return new WaitForSeconds (4);
		penjara.SetActive (true);
		gambarpenjara.SetActive (true);
		uangplayerbaru.uangplayer -= 500;

		uangbankk.uangbank += 500;
	}
	IEnumerator hidegambarpenjara(){
		yield return new WaitForSeconds (6);
		gambarpenjara.SetActive (false);
	}
	IEnumerator delaydetectjail(){
		yield return new WaitForSeconds (2);
		namavar.Redplayerpos -= 8;
		namavar.Counterpos++;
	}		
	IEnumerator delaygambarpajak(){
		yield return new WaitForSeconds (1);
		showgambarpajak.SetActive (true);
		yield return new WaitForSeconds (3);
		showgambarpajak.SetActive (false);
	}
	IEnumerator delaygambarbonusuang(){
		yield return new WaitForSeconds (1);
		showgambarbonus.SetActive (true);
		yield return new WaitForSeconds (3);
		showgambarbonus.SetActive (false);
	}
	//paneldenda
	IEnumerator dendajawatimur(){
		yield return new WaitForSeconds (1);
		paneldenda [0].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[0].SetActive (false);
	}
	IEnumerator dendajawatengah(){
		yield return new WaitForSeconds (1);
		paneldenda [1].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[1].SetActive (false);
	}
	IEnumerator dendajawabarat(){
		yield return new WaitForSeconds (1);
		paneldenda [2].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[2].SetActive (false);
	}
	IEnumerator dendayogyakarta(){
		yield return new WaitForSeconds (1);
		paneldenda [3].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[3].SetActive (false);
	}
	IEnumerator dendasolo(){
		yield return new WaitForSeconds (1);
		paneldenda [4].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[4].SetActive (false);
	}
	IEnumerator dendasumut(){
		yield return new WaitForSeconds (1);
		paneldenda [5].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[5].SetActive (false);
	}
	IEnumerator dendasumsel(){
		yield return new WaitForSeconds (1);
		paneldenda [6].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[6].SetActive (false);
	}
	IEnumerator dendasumbar(){
		yield return new WaitForSeconds (1);
		paneldenda [7].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[7].SetActive (false);
	}
	IEnumerator dendapadang(){
		yield return new WaitForSeconds (1);
		paneldenda [8].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[8].SetActive (false);
	}
	IEnumerator dendapalembang(){
		yield return new WaitForSeconds (1);
		paneldenda [9].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[9].SetActive (false);
	}
	IEnumerator dendasulbar(){
		yield return new WaitForSeconds (1);
		paneldenda [10].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[10].SetActive (false);
	}
	IEnumerator dendasulsel(){
		yield return new WaitForSeconds (1);
		paneldenda [11].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[11].SetActive (false);
	}
	IEnumerator dendasulut(){
		yield return new WaitForSeconds (1);
		paneldenda [12].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[12].SetActive (false);
	}
	IEnumerator dendasulteng(){
		yield return new WaitForSeconds (1);
		paneldenda [13].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[13].SetActive (false);
	}
	IEnumerator dendasultenggara(){
		yield return new WaitForSeconds (1);
		paneldenda [14].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[14].SetActive (false);
	}
	IEnumerator dendakalbar(){
		yield return new WaitForSeconds (1);
		paneldenda [15].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[15].SetActive (false);
	}
	IEnumerator dendakalsel(){
		yield return new WaitForSeconds (1);
		paneldenda [16].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[16].SetActive (false);
	}
	IEnumerator dendakaltim(){
		yield return new WaitForSeconds (1);
		paneldenda [17].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[17].SetActive (false);
	}
	IEnumerator dendakalut(){
		yield return new WaitForSeconds (1);
		paneldenda [18].SetActive (true);
		yield return new WaitForSeconds (3);
		paneldenda[18].SetActive (false);
	}
	//trigger cekkotak
	public void OnTriggerEnter(Collider collider){
		if (namavar.Counterpos == namavar.targetpos) {
			

			if (collider.gameObject.name == "pajak") {
				StartCoroutine (delaygambarpajak ());
				uangplayerbaru.uangplayer -= 750;

				uangbankk.uangbank += 750;
			}
			if (collider.gameObject.name == "bonusuang") {
				soundcoinbonus.PlayOneShot (coinbonussound);
				StartCoroutine (delaygambarbonusuang ());
				uangplayerbaru.uangplayer += 1200;

				uangbankk.uangbank -= 1200;
			}
			if (collider.gameObject.name == "backtojail") {
				StartCoroutine (delaydetectjail ());
				StartCoroutine (delaybacktojail ());
				StartCoroutine (hidegambarpenjara ());
			}



			if (collider.gameObject.name == "1") {
				langkah.PlayOneShot (langkahsound);
				if (cek.rk1 [0].activeSelf == true) {
					rumahkartu1.SetActive (false);
					StartCoroutine (dendajawatimur ());
					uangplayerbaru.uangplayer -= 200;
					uangcomputerbaru.uangcomputer += 200;
					setcount ();
				}
				if (cek.rk1 [1].activeSelf == true) {
					rumahkartu1.SetActive (false);
					uangplayerbaru.uangplayer -= 200;
					uangcomputerbaru.uangcomputer += 200;
					setcount ();
				}
				if (cek.rk1 [2].activeSelf == true) {
					rumahkartu1.SetActive (false);
					uangplayerbaru.uangplayer -= 200;
					uangcomputerbaru.uangcomputer += 200;
					setcount ();
				} else
					StartCoroutine (waktu_rumahk1 ());
			}





			if (collider.gameObject.name == "2") {
				langkah.PlayOneShot (langkahsound);
				if (cek.rk2 [0].activeSelf == true) {
					rumahkartu2.SetActive (false);
					StartCoroutine (dendajawatengah());
					uangplayerbaru.uangplayer -= 250;
					uangcomputerbaru.uangcomputer += 250;
					setcount ();
				}
				if (cek.rk2 [1].activeSelf == true) {
					rumahkartu2.SetActive (false);
					uangplayerbaru.uangplayer -= 250;
					uangcomputerbaru.uangcomputer += 250;
					setcount ();
				}
				if (cek.rk2 [2].activeSelf == true) {
					rumahkartu1.SetActive (false);
					uangplayerbaru.uangplayer -= 250;
					uangcomputerbaru.uangcomputer += 250;
					setcount ();
				} else
					StartCoroutine (waktu_rumahk2 ());

			}
			if (collider.gameObject.name == "3") {
				langkah.PlayOneShot (langkahsound);
				if (cek.rk3 [0].activeSelf == true) {
					rumahkartu3.SetActive (false);
					StartCoroutine (dendajawabarat ());
					uangplayerbaru.uangplayer -= 300;
					uangcomputerbaru.uangcomputer += 300;
					setcount ();
				} 
			
				if (cek.rk3 [1].activeSelf == true) {
					rumahkartu3.SetActive (false);
					uangplayerbaru.uangplayer -= 300;
					uangcomputerbaru.uangcomputer += 300;
					setcount ();
				}

				
				if (cek.rk3 [2].activeSelf == true) {
					rumahkartu3.SetActive (false);
					uangplayerbaru.uangplayer -= 300;
					uangcomputerbaru.uangcomputer += 300;
					setcount ();
				} else if (cek.rk3 [0].activeSelf == true) {
					rumahkartu3.SetActive (false);
				}
				else if (cek.rk3 [1].activeSelf == true) {
					rumahkartu3.SetActive (false);
				}
				else if (cek.rk3 [2].activeSelf == true) {
					rumahkartu3.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk3 ());
			}	
			if (collider.gameObject.name == "pulaujawa") {
				langkah.PlayOneShot (langkahsound);
				if (cek.kotakpulaujawa [0].activeSelf == true) {
					kpulaujawa.SetActive (false);
					uangplayerbaru.uangplayer -= 450;
					uangcomputerbaru.uangcomputer += 450;
					setcount ();
				} else
					StartCoroutine (waktu_pulaujawa ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "5") {
				langkah.PlayOneShot (langkahsound);
				if (cek.rk5 [0].activeSelf == true) {
					rumahkartu5.SetActive (false);
					StartCoroutine (dendayogyakarta ());
					uangplayerbaru.uangplayer -= 350;
					uangcomputerbaru.uangcomputer += 350;
					setcount ();
				}
				if (cek.rk5 [1].activeSelf == true) {
					rumahkartu5.SetActive (false);
					uangplayerbaru.uangplayer -= 350;
					uangcomputerbaru.uangcomputer += 350;
					setcount ();
				}
				if (cek.rk5 [2].activeSelf == true) {
					rumahkartu5.SetActive (false);
					uangplayerbaru.uangplayer -= 350;
					uangcomputerbaru.uangcomputer += 350;
					setcount ();
				} 
				else if (cek.rk5 [0].activeSelf == true) {
					rumahkartu5.SetActive (false);
				}
				else if (cek.rk5 [1].activeSelf == true) {
					rumahkartu5.SetActive (false);
				}
				else if (cek.rk5 [2].activeSelf == true) {
					rumahkartu5.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk5 ());

			}
			if (collider.gameObject.name == "6") {
				langkah.PlayOneShot (langkahsound);
				if (cek.rk6 [0].activeSelf == true) {
					rumahkartu6.SetActive (false);
					StartCoroutine (dendasolo ());
					uangplayerbaru.uangplayer -= 400;
					uangcomputerbaru.uangcomputer += 400;
					setcount ();
				}
				if (cek.rk6 [1].activeSelf == true) {
					rumahkartu6.SetActive (false);
					uangplayerbaru.uangplayer -= 400;
					uangcomputerbaru.uangcomputer += 400;
					setcount ();
				}
				if (cek.rk6 [2].activeSelf == true) {
					rumahkartu6.SetActive (false);
					uangplayerbaru.uangplayer -= 400;
					uangcomputerbaru.uangcomputer += 400;
					setcount ();
				} 
				else if (cek.rk5 [0].activeSelf == true) {
					rumahkartu5.SetActive (false);
				}
				else if (cek.rk5 [1].activeSelf == true) {
					rumahkartu5.SetActive (false);
				}
				else if (cek.rk5 [2].activeSelf == true) {
					rumahkartu5.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk5 ());

			}
	
			if (collider.gameObject.name == "soal1") {
				
				StartCoroutine (delaysoal1 ());
				StartCoroutine (waktu_soal1 ());

				ambilsoal.Tampilsoal (Random.Range (0, 10));
			}
			if (collider.gameObject.name == "penjara") {
				penjarasound.PlayOneShot (soundpenjara);
				StartCoroutine (waktu_penjara ());
			}
			if (collider.gameObject.name == "8") {
				if (cek.rk8 [0].activeSelf == true) {
					rumahkartu8.SetActive (false);
					StartCoroutine (dendasumut ());
					uangplayerbaru.uangplayer -= 450;
					uangcomputerbaru.uangcomputer += 450;
					setcount ();
				}
				if (cek.rk8 [1].activeSelf == true) {
					rumahkartu8.SetActive (false);
					uangplayerbaru.uangplayer -= 450;
					uangcomputerbaru.uangcomputer += 450;
					setcount ();
				}
				if (cek.rk8 [2].activeSelf == true) {
					rumahkartu8.SetActive (false);
					uangplayerbaru.uangplayer -= 450;
					uangcomputerbaru.uangcomputer += 450;
					setcount ();
				} 
				else if (cek.rk8 [0].activeSelf == true) {
					rumahkartu8.SetActive (false);
				}
				else if (cek.rk8 [1].activeSelf == true) {
					rumahkartu8.SetActive (false);
				}
				else if (cek.rk8 [2].activeSelf == true) {
					rumahkartu8.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk8 ());

			}
			if (collider.gameObject.name == "9") {
				langkah.PlayOneShot (langkahsound);
				if (cek.rk9 [0].activeSelf == true) {
					rumahkartu9.SetActive (false);
					StartCoroutine (dendasumsel ());
					uangplayerbaru.uangplayer -= 500;
					uangcomputerbaru.uangcomputer += 500;
					setcount ();
				}
				if (cek.rk9 [1].activeSelf == true) {
					rumahkartu9.SetActive (false);
					uangplayerbaru.uangplayer -= 500;
					uangcomputerbaru.uangcomputer += 500;
					setcount ();
				}
				if (cek.rk9 [2].activeSelf == true) {
					rumahkartu9.SetActive (false);
					uangplayerbaru.uangplayer -= 500;
					uangcomputerbaru.uangcomputer += 500;
					setcount ();
				} 
				else if (cek.rk9 [0].activeSelf == true) {
					rumahkartu9.SetActive (false);
				}
				else if (cek.rk9 [1].activeSelf == true) {
					rumahkartu9.SetActive (false);
				}
				else if (cek.rk9 [2].activeSelf == true) {
					rumahkartu9.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk9 ());

			}
			if (collider.gameObject.name == "10") {
				langkah.PlayOneShot (langkahsound);
				if (cek.rk10 [0].activeSelf == true) {
					rumahkartu10.SetActive (false);
					StartCoroutine (dendasumbar ());
					uangplayerbaru.uangplayer -= 550;
					uangcomputerbaru.uangcomputer += 550;
					setcount ();
				}
				if (cek.rk10 [1].activeSelf == true) {
					rumahkartu10.SetActive (false);
					uangplayerbaru.uangplayer -= 550;
					uangcomputerbaru.uangcomputer += 550;
					setcount ();
				}
				if (cek.rk10 [2].activeSelf == true) {
					rumahkartu10.SetActive (false);
					uangplayerbaru.uangplayer -= 550;
					uangcomputerbaru.uangcomputer += 550;
					setcount ();
				} 
				else if (cek.rk10 [0].activeSelf == true) {
					rumahkartu10.SetActive (false);
				}
				else if (cek.rk10 [1].activeSelf == true) {
					rumahkartu10.SetActive (false);
				}
				else if (cek.rk10 [2].activeSelf == true) {
					rumahkartu10.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk10 ());

			}
			if (collider.gameObject.name == "soal2") {
				
				StartCoroutine (delaysoal1 ());
				langkah.PlayOneShot (langkahsound);

				StartCoroutine (waktu_soal2 ());
				ambilsoal.Tampilsoal (Random.Range (0, 10));
			}
			if (collider.gameObject.name == "pulausumatra") {
				langkah.PlayOneShot (langkahsound);
				if (cek.kotakpulausumatra [0].activeSelf == true) {
					kpulausumatra.SetActive (false);
					uangplayerbaru.uangplayer -= 450;
					uangcomputerbaru.uangcomputer += 450;
					setcount ();
				} else
					StartCoroutine (waktu_pulausumatra ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "12") {
				if (cek.rk12 [0].activeSelf == true) {
					rumahkartu12.SetActive (false);
					StartCoroutine (dendapadang ());
					uangplayerbaru.uangplayer -= 600;
					uangcomputerbaru.uangcomputer += 600;
					setcount ();
				}
				if (cek.rk12 [1].activeSelf == true) {
					rumahkartu12.SetActive (false);
					uangplayerbaru.uangplayer -= 600;
					uangcomputerbaru.uangcomputer += 600;
					setcount ();
				}
				if (cek.rk12 [2].activeSelf == true) {
					rumahkartu12.SetActive (false);
					uangplayerbaru.uangplayer -= 600;
					uangcomputerbaru.uangcomputer += 600;
					setcount ();
				} 
				else if (cek.rk12 [0].activeSelf == true) {
					rumahkartu12.SetActive (false);
				}
				else if (cek.rk12 [1].activeSelf == true) {
					rumahkartu12.SetActive (false);
				}
				else if (cek.rk12 [2].activeSelf == true) {
					rumahkartu12.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk12 ());

			}
			if (collider.gameObject.name == "13") {
				if (cek.rk13 [0].activeSelf == true) {
					rumahkartu13.SetActive (false);
					StartCoroutine (dendapalembang ());
					uangplayerbaru.uangplayer -= 650;
					uangcomputerbaru.uangcomputer += 650;
					setcount ();
				}
				if (cek.rk13 [1].activeSelf == true) {
					rumahkartu13.SetActive (false);
					uangplayerbaru.uangplayer -= 650;
					uangcomputerbaru.uangcomputer += 650;
					setcount ();
				}
				if (cek.rk13 [2].activeSelf == true) {
					rumahkartu13.SetActive (false);
					uangplayerbaru.uangplayer -= 650;
					uangcomputerbaru.uangcomputer += 650;
					setcount ();
				} 
				else if (cek.rk13 [0].activeSelf == true) {
					rumahkartu13.SetActive (false);
				}
				else if (cek.rk13 [1].activeSelf == true) {
					rumahkartu3.SetActive (false);
				}
				else if (cek.rk13 [2].activeSelf == true) {
					rumahkartu13.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk13 ());

			}
	
			if (collider.gameObject.name == "16") {
				if (cek.rk16 [0].activeSelf == true) {
					rumahkartu16.SetActive (false);
					StartCoroutine (dendasulbar());
					uangplayerbaru.uangplayer -= 700;
					uangcomputerbaru.uangcomputer += 700;
					setcount ();
				}
				if (cek.rk16 [1].activeSelf == true) {
					rumahkartu16.SetActive (false);
					uangplayerbaru.uangplayer -= 700;
					uangcomputerbaru.uangcomputer += 700;
					setcount ();
				}
				if (cek.rk16 [2].activeSelf == true) {
					rumahkartu16.SetActive (false);
					uangplayerbaru.uangplayer -= 700;
					uangcomputerbaru.uangcomputer += 700;
					setcount ();
				} 
				else if (cek.rk16 [0].activeSelf == true) {
					rumahkartu16.SetActive (false);
				}
				else if (cek.rk16 [1].activeSelf == true) {
					rumahkartu16.SetActive (false);
				}
				else if (cek.rk16 [2].activeSelf == true) {
					rumahkartu16.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk16 ());

			}
			if (collider.gameObject.name == "17") {
				if (cek.rk17 [0].activeSelf == true) {
					rumahkartu17.SetActive (false);
					StartCoroutine (dendasulsel());
					uangplayerbaru.uangplayer -= 750;
					uangcomputerbaru.uangcomputer += 750;
					setcount ();
				}
				if (cek.rk17 [1].activeSelf == true) {
					rumahkartu17.SetActive (false);
					uangplayerbaru.uangplayer -= 750;
					uangcomputerbaru.uangcomputer += 750;
					setcount ();
				}
				if (cek.rk17 [2].activeSelf == true) {
					rumahkartu17.SetActive (false);
					uangplayerbaru.uangplayer -= 750;
					uangcomputerbaru.uangcomputer += 750;
					setcount ();
				} 
				else if (cek.rk17 [0].activeSelf == true) {
					rumahkartu17.SetActive (false);
				}
				else if (cek.rk17 [1].activeSelf == true) {
					rumahkartu17.SetActive (false);
				}
				else if (cek.rk17 [2].activeSelf == true) {
					rumahkartu17.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk17 ());

			}
			if (collider.gameObject.name == "18") {
				if (cek.rk18 [0].activeSelf == true) {
					rumahkartu18.SetActive (false);
					StartCoroutine (dendasulut());
					uangplayerbaru.uangplayer -= 800;
					uangcomputerbaru.uangcomputer += 800;
					setcount ();
				}
				if (cek.rk18 [1].activeSelf == true) {
					rumahkartu18.SetActive (false);
					uangplayerbaru.uangplayer -= 800;
					uangcomputerbaru.uangcomputer += 800;
					setcount ();
				}
				if (cek.rk18 [2].activeSelf == true) {
					rumahkartu18.SetActive (false);
					uangplayerbaru.uangplayer -= 800;
					uangcomputerbaru.uangcomputer += 800;
					setcount ();
				} 
				else if (cek.rk18 [0].activeSelf == true) {
					rumahkartu18.SetActive (false);
				}
				else if (cek.rk18 [1].activeSelf == true) {
					rumahkartu18.SetActive (false);
				}
				else if (cek.rk18 [2].activeSelf == true) {
					rumahkartu18.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk18 ());

			}
			//pulausulewesi
			if (collider.gameObject.name == "pulausulewesi") {
				langkah.PlayOneShot (langkahsound);
				if (cek.kotakpulausulewesi [0].activeSelf == true) {
					kpulausulewesi.SetActive (false);
					uangplayerbaru.uangplayer -= 450;
					uangcomputerbaru.uangcomputer += 450;
					setcount ();
				} else
					StartCoroutine (waktu_pulausulewesi ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "20") {
				if (cek.rk20 [0].activeSelf == true) {
					rumahkartu20.SetActive (false);
					StartCoroutine (dendasulteng());
					uangplayerbaru.uangplayer -= 850;
					uangcomputerbaru.uangcomputer += 850;
					setcount ();
				}
				if (cek.rk20 [1].activeSelf == true) {
					rumahkartu20.SetActive (false);
					uangplayerbaru.uangplayer -= 850;
					uangcomputerbaru.uangcomputer += 850;
					setcount ();
				}
				if (cek.rk20 [2].activeSelf == true) {
					rumahkartu20.SetActive (false);
					uangplayerbaru.uangplayer -= 850;
					uangcomputerbaru.uangcomputer += 850;
					setcount ();
				} 
				else if (cek.rk20 [0].activeSelf == true) {
					rumahkartu20.SetActive (false);
				}
				else if (cek.rk20 [1].activeSelf == true) {
					rumahkartu20.SetActive (false);
				}
				else if (cek.rk20 [2].activeSelf == true) {
					rumahkartu20.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk20 ());
			}
			if (collider.gameObject.name == "21") {
				if (cek.rk21 [0].activeSelf == true) {
					rumahkartu21.SetActive (false);
					StartCoroutine (dendasultenggara());
					uangplayerbaru.uangplayer -= 900;
					uangcomputerbaru.uangcomputer += 900;
					setcount ();
				}
				if (cek.rk21 [1].activeSelf == true) {
					rumahkartu21.SetActive (false);
					uangplayerbaru.uangplayer -= 900;
					uangcomputerbaru.uangcomputer += 900;
					setcount ();
				}
				if (cek.rk21 [2].activeSelf == true) {
					rumahkartu21.SetActive (false);
					uangplayerbaru.uangplayer -= 900;
					uangcomputerbaru.uangcomputer += 900;
					setcount ();
				} 
				else if (cek.rk21 [0].activeSelf == true) {
					rumahkartu21.SetActive (false);
				}
				else if (cek.rk21 [1].activeSelf == true) {
					rumahkartu21.SetActive (false);
				}
				else if (cek.rk21 [2].activeSelf == true) {
					rumahkartu21.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk21 ());

			}
			if (collider.gameObject.name == "soal3") {
				
				StartCoroutine (delaysoal1 ());
				StartCoroutine (waktu_soal3 ());
				ambilsoal.Tampilsoal (Random.Range (0, 10));
			}
			if (collider.gameObject.name == "22") {
				if (cek.rk22 [0].activeSelf == true) {
					rumahkartu22.SetActive (false);
					StartCoroutine (dendakalbar());
					uangplayerbaru.uangplayer -= 950;
					uangcomputerbaru.uangcomputer += 950;
					setcount ();
				}
				if (cek.rk22 [1].activeSelf == true) {
					rumahkartu22.SetActive (false);
					uangplayerbaru.uangplayer -= 950;
					uangcomputerbaru.uangcomputer += 950;
					setcount ();
				}
				if (cek.rk22 [2].activeSelf == true) {
					rumahkartu22.SetActive (false);
					uangplayerbaru.uangplayer -= 950;
					uangcomputerbaru.uangcomputer += 950;
					setcount ();
				} 
				else if (cek.rk22 [0].activeSelf == true) {
					rumahkartu22.SetActive (false);
				}
				else if (cek.rk22 [1].activeSelf == true) {
					rumahkartu22.SetActive (false);
				}
				else if (cek.rk22 [2].activeSelf == true) {
					rumahkartu22.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk22 ());

			}
			if (collider.gameObject.name == "pajak") {

				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "24") {
				if (cek.rk24 [0].activeSelf == true) {
					rumahkartu24.SetActive (false);
					StartCoroutine (dendakalsel());
					uangplayerbaru.uangplayer -= 1000;
					uangcomputerbaru.uangcomputer += 1000;
					setcount ();
				}
				if (cek.rk24 [1].activeSelf == true) {
					rumahkartu24.SetActive (false);
					uangplayerbaru.uangplayer -= 1000;
					uangcomputerbaru.uangcomputer += 1000;
					setcount ();
				}
				if (cek.rk24 [2].activeSelf == true) {
					rumahkartu24.SetActive (false);
					uangplayerbaru.uangplayer -= 1000;
					uangcomputerbaru.uangcomputer += 1000;
					setcount ();
				} 
				else if (cek.rk24 [0].activeSelf == true) {
					rumahkartu24.SetActive (false);
				}
				else if (cek.rk24 [1].activeSelf == true) {
					rumahkartu24.SetActive (false);
				}
				else if (cek.rk24 [2].activeSelf == true) {
					rumahkartu24.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk24 ());

			}
			if (collider.gameObject.name == "pulaukalimantan") {
				langkah.PlayOneShot (langkahsound);
				if (cek.kotakpulaukalimantan [0].activeSelf == true) {
					kpulaukalimantan.SetActive (false);
					uangplayerbaru.uangplayer -= 450;
					uangcomputerbaru.uangcomputer += 450;
					setcount ();
				} else
					StartCoroutine (waktu_pulaukalimantan ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "soal4") {

				StartCoroutine (delaysoal1 ());
				StartCoroutine (waktu_soal4 ());
				ambilsoal.Tampilsoal (Random.Range (0, 30));
			}

			if (collider.gameObject.name == "27") {
				if (cek.rk27 [0].activeSelf == true) {
					rumahkartu27.SetActive (false);
					StartCoroutine (dendakaltim());
					uangplayerbaru.uangplayer -= 1050;
					uangcomputerbaru.uangcomputer += 1050;
					setcount ();
				}
				if (cek.rk27 [1].activeSelf == true) {
					rumahkartu27.SetActive (false);
					uangplayerbaru.uangplayer -= 1050;
					uangcomputerbaru.uangcomputer += 1050;
					setcount ();
				}
				if (cek.rk27 [2].activeSelf == true) {
					rumahkartu27.SetActive (false);
					uangplayerbaru.uangplayer -= 1050;
					uangcomputerbaru.uangcomputer += 1050;
					setcount ();
				} 
				else if (cek.rk27 [0].activeSelf == true) {
					rumahkartu27.SetActive (false);
				}
				else if (cek.rk27 [1].activeSelf == true) {
					rumahkartu27.SetActive (false);
				}
				else if (cek.rk27 [2].activeSelf == true) {
					rumahkartu27.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk27 ());

			}
			if (collider.gameObject.name == "28") {
				if (cek.rk28 [0].activeSelf == true) {
					rumahkartu28.SetActive (false);
					StartCoroutine (dendakalut());
					uangplayerbaru.uangplayer -= 1100;
					uangcomputerbaru.uangcomputer += 1100;
					setcount ();
				}
				if (cek.rk28 [1].activeSelf == true) {
					rumahkartu28.SetActive (false);
					uangplayerbaru.uangplayer -= 1100;
					uangcomputerbaru.uangcomputer += 1100;
					setcount ();
				}
				if (cek.rk28 [2].activeSelf == true) {
					rumahkartu28.SetActive (false);
					uangplayerbaru.uangplayer -= 1100;
					uangcomputerbaru.uangcomputer += 1100;
					setcount ();
				} 
				else if (cek.rk28 [0].activeSelf == true) {
					rumahkartu28.SetActive (false);
				}
				else if (cek.rk28 [1].activeSelf == true) {
					rumahkartu28.SetActive (false);
				}
				else if (cek.rk28 [2].activeSelf == true) {
					rumahkartu28.SetActive (false);
				}
				else
					StartCoroutine (waktu_rumahk28 ());

			}
		
			if (collider.gameObject.name == "penjara") {

				uangplayerbaru.uangplayer -= 500;
				counttext.text = " " + uangplayerbaru.uangplayer;
				uangbankk.uangbank += 500;
			
				StartCoroutine (showgambarpenjaraa ());

			}

		}
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
	
		if (collider.gameObject.name == "0") {
			soundcoinbonus.PlayOneShot (coinbonussound);
			uangplayerbaru.uangplayer += 1750;
			counttext.text = " " + uangplayerbaru.uangplayer;
			Debug.Log (uangplayerbaru.uangplayer);
			gambarstart.SetActive (true);
			uangbankk.uangbank -= 1750;
			StartCoroutine (gstart ());
		}

	}
	IEnumerator gstart(){
		yield return new WaitForSeconds (2);
		gambarstart.SetActive (false);

	}
	IEnumerator showgambarpenjaraa(){
		yield return new WaitForSeconds (1);
		gambarpenjara.SetActive (true);
		yield return new WaitForSeconds (4);
		gambarpenjara.SetActive (false);
	}
	public void OnTriggerExit(Collider col){
		if (col.gameObject.name == "penjara") {
			penjara.SetActive (false);
		}
		rumahkartu1.SetActive (false);
		rumahkartu2.SetActive (false);
		rumahkartu3.SetActive (false);
		kpulaujawa.SetActive (false);
		rumahkartu5.SetActive (false);
		rumahkartu6.SetActive (false);
		rumahkartu8.SetActive (false);
		rumahkartu9.SetActive (false);
		rumahkartu10.SetActive (false);
		kpulausumatra.SetActive (false);
		rumahkartu12.SetActive (false);
		rumahkartu13.SetActive (false);
		soal2.SetActive (false);
		rumahkartu16.SetActive (false);
		rumahkartu17.SetActive (false);
		rumahkartu18.SetActive (false);
		rumahkartu20.SetActive (false);
		kpulausulewesi.SetActive (false);
		rumahkartu21.SetActive (false);
		rumahkartu22.SetActive (false);
		kpulaukalimantan.SetActive (false);
		rumahkartu24.SetActive (false);
		rumahkartu27.SetActive (false);
		rumahkartu28.SetActive (false);
		soal1.SetActive (false);

		soal3.SetActive (false);
		soal4.SetActive (false);


	}
	public void restartwingame(){
		Application.LoadLevel ("boardgame");
		showpanelwin.SetActive (false);
	}

	// Update is called once per frame
	void Update () {

		if (uangplayerbaru.uangplayer < 0) {
			showpanelwin.SetActive (true);
			showflare.SetActive (true);
			counttext.text = "Bangkrut";
		}
	

	
		if (uangplayerbaru.uangplayer < 9000) {
			Destroy (uangmono);
		}
		if (uangplayerbaru.uangplayer < 8000) {
			Destroy (uangmono2);
		}
		if (uangplayerbaru.uangplayer < 7000) {
			Destroy (uangmono3);
		}
		if (uangplayerbaru.uangplayer < 6000) {
			Destroy (uangmono6);
		}
		if (uangplayerbaru.uangplayer < 5000) {
			Destroy (uangmono5);
		}
		if (uangplayerbaru.uangplayer < 4000) {
			Destroy (uangmono4);
		}
		if (uangplayerbaru.uangplayer < 3000) {
			Destroy (uangmono7);
		}
		if (uangplayerbaru.uangplayer < 2000) {
			Destroy (uangmono8);
		}

		}



	}


